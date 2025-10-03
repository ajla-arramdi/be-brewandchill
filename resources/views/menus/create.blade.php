<h1>Tambah Menu</h1>
<form action="{{ route('menus.store') }}" method="POST">
    @csrf
    <label>Nama:</label>
    <input type="text" name="name" required><br>
    
    <label>Deskripsi:</label>
    <textarea name="description"></textarea><br>
    
    <label>Harga:</label>
    <input type="number" name="price" required><br>
    
    <button type="submit">Simpan</button>
</form>

