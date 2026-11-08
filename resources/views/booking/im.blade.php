<div class="container">
    <h3>Import Dish</h3>

    <form method="POST" action="{{ route('booking.storeIm') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label">Paste content here</label>
            <textarea name="content"
                      rows="20"
                      class="form-control"
                      placeholder="Paste dish list here..."
                      required></textarea>
        </div>

        <button class="btn btn-primary">
            Import Dishes
        </button>
    </form>
</div>