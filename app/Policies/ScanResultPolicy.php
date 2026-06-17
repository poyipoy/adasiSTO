<?php

namespace App\Policies;

use App\Models\ScanResult;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ScanResultPolicy
{
    public function view(User $user, ScanResult $scanResult): Response
    {
        return $user->isAdmin() || $scanResult->user_id === $user->id
            ? Response::allow()
            : Response::deny('Anda tidak memiliki akses untuk melihat data ini.');
    }

    public function update(User $user, ScanResult $scanResult): Response
    {
        return $user->isAdmin()
            ? Response::allow()
            : Response::deny('Anda tidak memiliki akses untuk mengubah data ini.');
    }

    public function delete(User $user, ScanResult $scanResult): Response
    {
        return $user->isAdmin() || $scanResult->user_id === $user->id
            ? Response::allow()
            : Response::deny('Anda tidak memiliki akses untuk menghapus data ini.');
    }
}
