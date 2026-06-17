import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const fromRoot = (...segments) => path.join(root, ...segments);

const copies = [
    ['node_modules/jquery/dist/jquery.min.js', 'public/vendor/jquery/jquery.min.js'],
    ['node_modules/datatables.net/js/jquery.dataTables.min.js', 'public/vendor/datatables/jquery.dataTables.min.js'],
    ['node_modules/datatables.net-dt/css/jquery.dataTables.min.css', 'public/vendor/datatables/jquery.dataTables.min.css'],
    ['node_modules/sweetalert2/dist/sweetalert2.all.min.js', 'public/vendor/sweetalert2/sweetalert2.all.min.js'],
    ['node_modules/sweetalert2/dist/sweetalert2.min.css', 'public/vendor/sweetalert2/sweetalert2.min.css'],
    ['node_modules/html5-qrcode/html5-qrcode.min.js', 'public/vendor/html5-qrcode/html5-qrcode.min.js'],
];

const chartJsCandidates = [
    'node_modules/chart.js/dist/chart.umd.min.js',
    'node_modules/chart.js/dist/chart.umd.js',
];

function copyFile(source, target) {
    const sourcePath = fromRoot(source);
    const targetPath = fromRoot(target);

    if (!fs.existsSync(sourcePath)) {
        throw new Error(`Missing vendor asset: ${source}`);
    }

    fs.mkdirSync(path.dirname(targetPath), { recursive: true });
    fs.copyFileSync(sourcePath, targetPath);
    console.log(`${source} -> ${target}`);
}

function copyDirectory(source, target) {
    const sourcePath = fromRoot(source);
    const targetPath = fromRoot(target);

    if (!fs.existsSync(sourcePath)) {
        throw new Error(`Missing vendor directory: ${source}`);
    }

    fs.rmSync(targetPath, { recursive: true, force: true });
    fs.mkdirSync(path.dirname(targetPath), { recursive: true });
    fs.cpSync(sourcePath, targetPath, { recursive: true });
    console.log(`${source} -> ${target}`);
}

for (const [source, target] of copies) {
    copyFile(source, target);
}

const chartJsSource = chartJsCandidates.find((candidate) => fs.existsSync(fromRoot(candidate)));

if (!chartJsSource) {
    throw new Error('Missing Chart.js UMD asset.');
}

copyFile(chartJsSource, 'public/vendor/chartjs/chart.umd.min.js');
copyDirectory('node_modules/@fontsource/inter', 'public/vendor/@fontsource/inter');

