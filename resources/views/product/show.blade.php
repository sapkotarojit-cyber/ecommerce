<form action="{{ route('cart.add') }}" method="POST" class="mt-4">
    @csrf
    <input type="hidden" name="varient_id" value="{{ $product->varients->first()->id ?? '' }}">
    <div class="flex items-center gap-4">
        <div class="flex items-center border border-gray-200 rounded-lg overflow-hidden">
            <button type="button" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 transition-colors text-[#1a2a6c] font-bold" onclick="decreaseQty()">
                <i class="fas fa-minus"></i>
            </button>
            <input type="number" name="qty" id="qty" value="1" min="1"
                   class="w-16 text-center border-0 focus:ring-0 py-2">
            <button type="button" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 transition-colors text-[#1a2a6c] font-bold" onclick="increaseQty()">
                <i class="fas fa-plus"></i>
            </button>
        </div>
        <button type="submit" class="flex-1 sm:flex-none px-8 py-3 bg-[#c9a84c] text-[#1a2a6c] font-semibold rounded-lg hover:bg-[#dbb95c] transition-all">
            <i class="fas fa-shopping-cart mr-2"></i> Add to Cart
        </button>
    </div>
</form>

<script>
function increaseQty() {
    let input = document.getElementById('qty');
    input.value = parseInt(input.value) + 1;
}
function decreaseQty() {
    let input = document.getElementById('qty');
    if (parseInt(input.value) > 1) {
        input.value = parseInt(input.value) - 1;
    }
}
</script>
