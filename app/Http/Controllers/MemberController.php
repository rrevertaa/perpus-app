<?php

namespace App\Http\Controllers;

use App\Models\Member; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MemberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    
        public function index(Request $request)
    {
        $members = Member::when($request->search, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('nis_nim', 'like', "%{$search}%")
                    ->orWhere('no_hp', 'like', "%{$search}%");
            });
        })->latest()->paginate(10)->withQueryString();

        
    return inertia('Members/Index', [
        'members' => $members,
        'search' => $request->search,
    ]);

    }

    /**
     * Endpoint JSON untuk live search anggota (dipanggil oleh Axios).
     */
    public function search(Request $request)
    {
        // Kata kunci dibaca dari query string, misalnya /members/search?q=ani
        $kataKunci = $request->query('q', '');

        // nama ATAU nis_nim yang cocok, maksimal 10 baris
        $members = Member::where(function ($query) use ($kataKunci) {
            $query->where('nama', 'like', "%{$kataKunci}%")
                ->orWhere('nis_nim', 'like', "%{$kataKunci}%");
        })
            ->latest()
            ->limit(10)
            ->get(['id', 'nama', 'nis_nim', 'no_hp', 'foto']);

        
        return response()->json($members);
    }

    /**
     * Endpoint JSON untuk validasi realtime: apakah NIS/NIM masih tersedia?
     */
        public function cekNis(Request $request)
    {
        // NIS/NIM yang sedang diketik, misalnya /members/cek-nis?nis_nim=2023001
        $nisNim = $request->query('nis_nim', '');

        // Abaikan ID anggota yang sedang diedit (kosong pada form tambah)
        $abaikanId = $request->query('abaikan');

        // Apakah ada anggota lain yang sudah memakai NIS/NIM ini?
        $sudahDipakai = Member::where('nis_nim', $nisNim)
            ->when($abaikanId, function ($query, $id) {
                // Jika ada ID yang diabaikan, jangan hitung anggota itu sendiri
                $query->where('id', '!=', $id);
            })
            ->exists();

        // Dibalas sebagai JSON, misalnya {"tersedia": true}
        return response()->json([
            'tersedia' => ! $sudahDipakai,
        ]);
    }




    /**
     * Show the form for creating a new resource.
     */
    
    public function create()
    {
        return inertia('Members/Create');
    }

    public function store(Request $request)
    {
        // 1. Validasi data teks dan berkas foto
        $validated = $request->validate([
            'nama' => 'required|max:255',
            'nis_nim' => 'required|max:50|unique:members',
            'no_hp' => 'nullable|digits_between:8,15',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:1024',
        ]);

        // 2. Jika ada berkas foto yang diunggah, simpan ke storage/app/public/members
        // lalu catat path-nya untuk disimpan di database
        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('members', 'public');
        }

        // 3. Simpan sebagai baris baru di tabel members
        Member::create($validated);

        // 4. Kembali ke daftar anggota, sertakan flash message
        return redirect()->route('members.index')
            ->with('success', 'Anggota berhasil ditambahkan');
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    
    public function edit(Member $member)
    {
        // $member sudah otomatis berisi data anggota yang akan diedit (Route Model Binding)
        return inertia('Members/Edit', [
            'member' => $member,
        ]);
    }

    public function update(Request $request, Member $member)
    {
        // Aturan sama seperti store(), dengan pengecualian unique agar NIS/NIM milik sendiri tidak dianggap duplikat
        $validated = $request->validate([
            'nama' => 'required|max:255',
            'nis_nim' => 'required|max:50|unique:members,nis_nim,' . $member->id,
            'no_hp' => 'nullable|digits_between:8,15',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:1024',
        ]);

        if ($request->hasFile('foto')) {
            // Ada foto baru: hapus foto lama (jika ada) agar tidak menumpuk di storage
            if ($member->foto) {
                Storage::disk('public')->delete($member->foto);
            }

            // Simpan foto baru, catat path-nya
            $validated['foto'] = $request->file('foto')->store('members', 'public');
        } else {
            // Tidak ada foto baru: buang kunci 'foto' agar foto lama TIDAK tertimpa kosong
            unset($validated['foto']);
        }

        $member->update($validated);

        return redirect()->route('members.index')
            ->with('success', 'Anggota berhasil diperbarui');
    }


    /**
     * Remove the specified resource from storage.
     */
    
    public function destroy(Member $member)
    {
        // Hapus berkas foto dari storage (jika ada), baru hapus barisnya dari database
        if ($member->foto) {
            Storage::disk('public')->delete($member->foto);
        }

        $member->delete();

        return redirect()->route('members.index')->with('success', 'Anggota berhasil dihapus');
    }

}
