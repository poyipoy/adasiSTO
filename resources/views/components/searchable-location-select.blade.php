<style>
    .sto-location-select-native {
        position: absolute !important;
        width: 1px !important;
        height: 1px !important;
        padding: 0 !important;
        margin: 0 !important;
        opacity: 0 !important;
        pointer-events: none !important;
    }

    .sto-location-select-trigger {
        position: relative;
        width: 100%;
        min-height: 32px;
        padding-right: 30px;
        text-align: left;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        background: var(--surface, #fff);
        color: var(--text, #20252b);
        cursor: pointer;
    }

    .sto-location-select-trigger::after {
        position: absolute;
        top: 50%;
        right: 11px;
        width: 7px;
        height: 7px;
        border-right: 1.5px solid currentColor;
        border-bottom: 1.5px solid currentColor;
        content: "";
        transform: translateY(-65%) rotate(45deg);
        pointer-events: none;
    }

    .sto-location-select-trigger:disabled {
        cursor: not-allowed;
        opacity: .6;
    }

    .sto-location-select-overlay {
        position: fixed;
        inset: 0;
        z-index: 2200;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 16px;
        background: rgba(15, 20, 26, .48);
    }

    .sto-location-select-overlay.is-open {
        display: flex;
    }

    .sto-location-select-dialog {
        width: min(500px, 100%);
        max-height: min(80vh, 640px);
        display: flex;
        flex-direction: column;
        background: var(--surface, #fff);
        border: 1px solid var(--border, #bec5ce);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 12px 32px rgba(15, 20, 26, .2);
    }

    .sto-location-select-header {
        min-height: 54px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 16px;
        border-bottom: 1px solid var(--border-light, #d9dee5);
    }

    .sto-location-select-title {
        min-width: 0;
        margin: 0;
        font-size: 16px;
        font-weight: 700;
    }

    .sto-location-select-search {
        padding: 12px 16px;
        border-bottom: 1px solid var(--border-light, #d9dee5);
        background: #fafbfc;
    }

    .sto-location-select-list {
        min-height: 80px;
        max-height: 520px;
        padding: 8px 16px 16px;
        overflow-y: auto;
        overscroll-behavior: contain;
    }

    .sto-location-select-option {
        width: 100%;
        min-height: 44px;
        padding: 12px;
        border: 0;
        border-bottom: 1px solid var(--border-light, #e3e7ec);
        background: transparent;
        color: var(--text, #20252b);
        text-align: left;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: background-color .1s ease, color .1s ease;
    }

    .sto-location-select-option:hover,
    .sto-location-select-option:focus-visible {
        background: #f4f8ff;
        outline: 0;
    }

    .sto-location-select-option.is-active {
        margin-bottom: 1px;
        border-bottom-color: transparent;
        border-radius: 6px;
        background: var(--primary, #006bc7);
        color: #fff;
        font-weight: 600;
    }

    .sto-location-select-empty {
        padding: 24px 12px;
        color: var(--text-muted, #657080);
        text-align: center;
        font-size: 12px;
    }

    @media (max-width: 768px) {
        .sto-location-select-overlay {
            align-items: flex-end;
            padding: 0;
        }

        .sto-location-select-dialog {
            width: 100%;
            max-height: min(85vh, 640px);
            border-radius: 16px 16px 0 0;
        }

        .sto-location-select-option {
            min-height: 44px;
        }
    }
</style>

<script>
    (() => {
        if (window.StoSearchableLocationSelect) return;

        const instances = new WeakMap();

        class SearchableLocationSelect {
            constructor(select) {
                this.select = select;
                this.title = select.dataset.searchTitle || this.resolveLabel() || 'Pilih Location';
                this.trigger = this.createTrigger();
                this.overlay = this.createOverlay();
                this.searchInput = this.overlay.querySelector('[data-location-search-input]');
                this.list = this.overlay.querySelector('[data-location-search-list]');
                this.observer = new MutationObserver(() => this.sync());

                select.classList.add('sto-location-select-native');
                select.insertAdjacentElement('afterend', this.trigger);
                document.body.appendChild(this.overlay);

                this.bind();
                this.observer.observe(select, {
                    childList: true,
                    subtree: true,
                    attributes: true,
                    attributeFilter: ['disabled', 'hidden', 'label', 'selected'],
                });
                this.sync();
            }

            resolveLabel() {
                if (!this.select.id) return '';
                return document.querySelector(`label[for="${CSS.escape(this.select.id)}"]`)?.textContent.trim() || '';
            }

            createTrigger() {
                const trigger = document.createElement('button');
                trigger.type = 'button';
                trigger.className = 'form-control sto-location-select-trigger';
                trigger.setAttribute('aria-haspopup', 'dialog');
                trigger.setAttribute('aria-expanded', 'false');
                return trigger;
            }

            createOverlay() {
                const overlay = document.createElement('div');
                overlay.className = 'sto-location-select-overlay';
                overlay.innerHTML = `
                    <div class="sto-location-select-dialog" role="dialog" aria-modal="true">
                        <div class="sto-location-select-header">
                            <h3 class="sto-location-select-title"></h3>
                            <button type="button" class="btn-icon" data-location-search-close aria-label="Tutup">
                                <svg aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24" style="width:16px;height:16px;">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6 6 18"></path>
                                </svg>
                            </button>
                        </div>
                        <div class="sto-location-select-search">
                            <input type="search" class="form-control" data-location-search-input
                                placeholder="Cari lokasi..." autocomplete="off">
                        </div>
                        <div class="sto-location-select-list" data-location-search-list role="listbox"></div>
                    </div>`;
                overlay.querySelector('.sto-location-select-title').textContent = this.title;
                return overlay;
            }

            bind() {
                this.trigger.addEventListener('click', () => this.open());
                this.select.addEventListener('change', () => this.sync());
                this.searchInput.addEventListener('input', () => this.render(this.searchInput.value));
                this.overlay.querySelector('[data-location-search-close]').addEventListener('click', () => this.close());
                this.overlay.addEventListener('click', event => {
                    if (event.target === this.overlay) this.close();
                });
                this.overlay.addEventListener('keydown', event => {
                    if (event.key === 'Escape') this.close();
                });
                this.select.form?.addEventListener('reset', () => setTimeout(() => this.sync(), 0));
            }

            availableOptions() {
                return Array.from(this.select.options).filter(option => !option.hidden && !option.disabled);
            }

            sync() {
                const selected = this.select.options[this.select.selectedIndex];
                this.trigger.textContent = selected?.textContent.trim()
                    || this.select.dataset.searchPlaceholder
                    || 'Pilih Location';
                this.trigger.disabled = this.select.disabled;
                this.trigger.setAttribute('aria-disabled', String(this.select.disabled));
                if (this.overlay.classList.contains('is-open')) this.render(this.searchInput.value);
            }

            open() {
                if (this.select.disabled) return;
                this.searchInput.value = '';
                this.render('');
                this.overlay.classList.add('is-open');
                this.trigger.setAttribute('aria-expanded', 'true');
                setTimeout(() => this.searchInput.focus(), 0);
            }

            close() {
                this.overlay.classList.remove('is-open');
                this.trigger.setAttribute('aria-expanded', 'false');
                this.trigger.focus();
            }

            render(query) {
                const normalizedQuery = String(query || '').trim().toLocaleLowerCase('id-ID');
                const options = this.availableOptions().filter(option => {
                    if (!normalizedQuery) return true;
                    return option.textContent.trim().toLocaleLowerCase('id-ID').includes(normalizedQuery);
                });

                this.list.replaceChildren();
                if (!options.length) {
                    const empty = document.createElement('div');
                    empty.className = 'sto-location-select-empty';
                    empty.textContent = 'Location tidak ditemukan.';
                    this.list.appendChild(empty);
                    return;
                }

                options.forEach(option => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'sto-location-select-option';
                    button.textContent = option.textContent.trim();
                    button.setAttribute('role', 'option');
                    button.setAttribute('aria-selected', String(option.selected));
                    if (option.selected) button.classList.add('is-active');
                    button.addEventListener('click', () => {
                        this.select.value = option.value;
                        this.select.dispatchEvent(new Event('change', { bubbles: true }));
                        this.close();
                    });
                    this.list.appendChild(button);
                });
            }
        }

        window.StoSearchableLocationSelect = {
            enhance(select) {
                if (!(select instanceof HTMLSelectElement) || instances.has(select)) return instances.get(select);
                const instance = new SearchableLocationSelect(select);
                instances.set(select, instance);
                return instance;
            },
            refresh(target) {
                const select = typeof target === 'string' ? document.querySelector(target) : target;
                instances.get(select)?.sync();
            },
            init(root = document) {
                root.querySelectorAll('select[data-searchable-location]').forEach(select => this.enhance(select));
            },
        };

        const initialize = () => window.StoSearchableLocationSelect.init();
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initialize, { once: true });
        } else {
            initialize();
        }
    })();
</script>
