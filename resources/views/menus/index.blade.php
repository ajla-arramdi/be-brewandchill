<h1>Daftar Menu</h1>
<a href="{{ route('menus.create') }}">Tambah Menu</a>
<table border="1">
    <tr>
        <th>Nama</th>
        <th>Deskripsi</th>
        <th>Harga</th>
        <th>Aksi</th>
    </tr>
    @foreach($menus as $menu)
    <tr>
        <td>{{ $menu->name }}</td>
        <td>{{ $menu->description }}</td>
        <td>{{ $menu->price }}</td>
        <td>
            <a href="{{ route('menus.edit', $menu->id) }}">Edit</a>
            <form action="{{ route('menus.destroy', $menu->id) }}" method="POST" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit">Hapus</button>
            </form>
        </td>
    </tr>
    @endforeach
</table>
