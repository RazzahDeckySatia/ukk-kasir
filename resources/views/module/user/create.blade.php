@extends('main')
@section('title', '| Product Create')

@section('content')

<div class="row">
    @if ($errors->any())
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            Swal.fire({
                icon: "error",
                title: "Oops...",
                html: `{!! implode('<br>', $errors->all()) !!}`,
                confirmButtonColor: "#d33",
                confirmButtonText: "Tutup"
            });
        });
    </script>
@endif
    <form action="{{ route('user.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
      
        <!-- Nama -->
        <div class="mb-3">
          <label for="name" class="form-label">Nama</label>
          <input type="text" class="form-control border-secondary" id="name" name="name" required value="{{ old('name') }}">
        </div>
      
        <!-- Email -->
        <div class="mb-3">
          <label for="email" class="form-label">Email</label>
          <input type="email" class="form-control border-secondary" id="email" name="email" required value="{{ old('email') }}">
        </div>
      
        <!-- password -->
        <div class="mb-3">
          <label for="password" class="form-label">password</label>
          <input type="password" class="form-control border-secondary" id="password" name="password" required>
        </div>
      
        <!-- role -->
        <div class="mb-3">
            <label for="role" class="form-label">Role</label>
            <select name="role" id="role" class="form-select" >
                <option selected disabled hidden>Pilih Role</option>
                <option value="admin">Admin</option>
                <option value="employee">employee</option>
            </select>
        </div>

      
        <!-- Tombol Submit -->
        <button type="submit" class="btn btn-primary">Simpan</button>
      </form>      
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.getElementById("todo-form").addEventListener("submit", function (event) {
        event.preventDefault(); // Mencegah reload
        let form = this;

        fetch(form.action, {
            method: form.method,
            body: new FormData(form),
            headers: {
                "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    title: "Sukses!",
                    text: data.message,
                    icon: "success",
                    confirmButtonText: "OK"
                }).then(() => {
                    location.reload(); // Reload halaman setelah SweetAlert ditutup
                });
            }
        })
        .catch(error => console.error("Error:", error));
    });
</script>
@endsection
