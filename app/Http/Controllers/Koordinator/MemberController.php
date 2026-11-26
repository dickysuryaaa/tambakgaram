<?php

namespace App\Http\Controllers\Koordinator;

use App\Http\Controllers\Controller;
use App\Models\Member;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** FR-04: CRUD data anggota dan akun login. Nomor telepon hanya tampil di area ini. */
class MemberController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $role = $request->query('peran');
        $status = $request->query('status');

        $members = Member::withCount('ponds')
            ->when($q !== '', fn ($query) => $query->where(fn ($w) => $w
                ->where('name', 'like', "%$q%")->orWhere('username', 'like', "%$q%")->orWhere('phone', 'like', "%$q%")))
            ->when(in_array($role, [Member::ROLE_PETAMBAK, Member::ROLE_KOORDINATOR_ADMIN], true), fn ($query) => $query->where('role', $role))
            ->when(in_array($status, ['aktif', 'nonaktif'], true), fn ($query) => $query->where('status', $status))
            ->orderBy('name')->paginate(15)->withQueryString();

        return view('koordinator.anggota.index', compact('members', 'q', 'role', 'status'));
    }

    public function create()
    {
        return view('koordinator.anggota.form', ['member' => new Member(['role' => Member::ROLE_PETAMBAK, 'status' => 'aktif'])]);
    }

    public function store(Request $request)
    {
        $member = Member::create($this->validated($request, null));

        return redirect()->route('k.members.index')->with('success', 'Anggota "'.$member->name.'" berhasil ditambahkan.');
    }

    public function edit(Member $member)
    {
        return view('koordinator.anggota.form', compact('member'));
    }

    public function update(Request $request, Member $member)
    {
        $data = $this->validated($request, $member);

        // Cegah koordinator mengunci dirinya sendiri.
        if ($member->is($request->user()) && ($data['role'] !== $member->role || $data['status'] !== 'aktif')) {
            return back()->withInput()->with('error', 'Anda tidak dapat menonaktifkan atau mengubah peran akun Anda sendiri.');
        }

        $member->update($data);

        return redirect()->route('k.members.index')->with('success', 'Data anggota "'.$member->name.'" berhasil diperbarui.');
    }

    public function destroy(Request $request, Member $member)
    {
        if ($member->is($request->user())) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        try {
            $member->delete();
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return back()->with('error', 'Anggota ini masih punya tambak atau catatan, jadi tidak dapat dihapus. Ubah statusnya menjadi Tidak Aktif.');
            }
            throw $e;
        }

        return redirect()->route('k.members.index')->with('success', 'Anggota berhasil dihapus.');
    }

    private function validated(Request $request, ?Member $member): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9._-]+$/', Rule::unique('members', 'username')->ignore($member?->member_id, 'member_id')],
            'password' => [$member ? 'nullable' : 'required', 'string', 'min:8', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+\-\s()]+$/'],
            'role' => ['required', Rule::in([Member::ROLE_PETAMBAK, Member::ROLE_KOORDINATOR_ADMIN])],
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
        ], [
            'username.regex' => 'Nama pengguna hanya boleh berisi huruf, angka, titik, garis bawah, atau strip.',
            'username.unique' => 'Nama pengguna ini sudah dipakai anggota lain.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'phone.regex' => 'Nomor telepon hanya boleh berisi angka, spasi, +, -, dan tanda kurung.',
        ], [
            'name' => 'Nama', 'username' => 'Nama pengguna', 'password' => 'Kata sandi', 'phone' => 'Nomor telepon', 'role' => 'Peran', 'status' => 'Status',
        ]);

        // Pemilik tambak harus tetap petambak.
        if ($member && $data['role'] !== Member::ROLE_PETAMBAK && $member->ponds()->exists()) {
            abort(back()->withInput()->withErrors(['role' => 'Anggota ini masih memiliki tambak. Pindahkan tambaknya dulu sebelum mengubah peran.']));
        }

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }
        $data['phone'] = filled($data['phone'] ?? null) ? $data['phone'] : null;

        return $data;
    }
}
