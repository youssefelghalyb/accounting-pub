<x-dashboard>
<div class="item-row" data-item="1">
    <input type="hidden" name="items[1][product_id]" class="product-id">
    
    <!-- Button to trigger drawer -->
    <button type="button" 
            class="product-select-btn w-full px-3 py-2 border rounded-lg hover:bg-gray-50"
            data-context="1">
        <span class="product-display text-gray-400">Select Product</span>
    </button>
</div>

<!-- Component -->
<x-searchable-select-drawer
    drawer-id="productDrawer"
    title="Select Product"
    api-url="{{ route('api.drawer.products.search') }}"
    trigger-selector=".product-select-btn"
    :filters="[/*...*/]"
    :display-fields="[/*...*/]"
    on-select-callback="onProductSelected"
/>

@push('scripts')
<script>
async function onProductSelected(productId, itemId) {
    // Fetch full product details
    const response = await fetch(`/api/products/${productId}`);
    const product = await response.json();
    
    // Update the row
    const row = document.querySelector(`[data-item="${itemId}"]`);
    if (row) {
        row.querySelector('.product-id').value = product.id;
        row.querySelector('.product-display').textContent = product.name;
        row.querySelector('.product-display').classList.remove('text-gray-400');
        row.querySelector('.product-display').classList.add('text-gray-900');
        row.querySelector('.item-price').value = product.price;
    }
}
</script>
@endpush
</x-dashboard>