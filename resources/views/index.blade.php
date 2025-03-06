@extends('layouts.master')

@section('content')
<div class="container">
    <div class="card mb-4">
        <div class="card-header">
            Boosting Dashboard
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    {{-- <h5>API Balance: {{ $balance }} {{ $currency }}</h5> --}}
                </div>
                <div class="col-md-6">
                    {{-- <h5>Your Balance: ${{ number_format($userBalance, 2) }}</h5> --}}
                </div>
            </div>

            @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            @if (empty($services))
            <div class="alert alert-warning">No services available from the No1SMMPanel API. Please check your API key
                or connection.</div>
            @else
            <form action="{{ route('boosting.store') }}" method="POST" class="mb-4">
                @csrf
                <div class="mb-3">
                    <label for="categorySelect" class="form-label">Category</label>
                    <select name="category" id="categorySelect"
                        class="form-control @error('category') is-invalid @enderror" required>
                        <option value="">Select a category</option>
                        @foreach($categories as $category)
                        <option value="{{ $category }}">{{ $category }}</option>
                        @endforeach
                    </select>
                    @error('category')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="serviceSelect" class="form-label">Service</label>
                    <select name="service" id="serviceSelect"
                        class="form-control @error('service') is-invalid @enderror" required>
                        <option value="">Select a service</option>
                    </select>
                    @error('service')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="linkInput" class="form-label">Link</label>
                    <input type="url" name="link" id="linkInput"
                        class="form-control @error('link') is-invalid @enderror" value="{{ old('link') }}" required>
                    @error('link')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="quantityInput" class="form-label">Quantity</label>
                    <input type="number" name="quantity" id="quantityInput"
                        class="form-control @error('quantity') is-invalid @enderror" value="{{ old('quantity') }}"
                        required min="1">
                    @error('quantity')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Total Price</label>
                    <div id="totalPrice" class="form-control-plaintext fw-bold">$0.00</div>
                </div>

                <button type="submit" class="btn btn-primary">Place Order</button>
            </form>
            @endif

            <h5>Your Orders</h5>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Service</th>
                        <th>Price</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- @forelse($orders as $order)
                    <tr>
                        <td>{{ $order->id }}</td>
                        <td>{{ $order->service_id }}</td>
                        <td>${{ number_format($order->charged_price, 2) }}</td>
                        <td>{{ $order->status }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center">No orders yet</td>
                    </tr>
                    @endforelse --}}
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    const services = @json($services);
 const markupPercentage = {{ $markupPercentage / 100 }};
 console.log('Services from No1SMMPanel API:', JSON.stringify(services, null, 2));
 console.log('Markup Percentage:', markupPercentage);
</script>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
 const categorySelect = document.getElementById('categorySelect');
 const serviceSelect = document.getElementById('serviceSelect');
 const quantityInput = document.getElementById('quantityInput');
 const totalPriceDiv = document.getElementById('totalPrice');

 let selectedService = null;

 function populateServices(category) {
 console.log('Populating services for category:', category);
 serviceSelect.innerHTML = '<option value="">Select a service</option>';
 const filteredServices = services.filter(service => service.category === category);
 console.log('Filtered Services for ' + category + ':', JSON.stringify(filteredServices, null, 2));

 if (filteredServices.length === 0) {
 serviceSelect.innerHTML += '<option value="">No services available</option>';
 } else {
 filteredServices.forEach(service => {
 const originalPrice = parseFloat(service.rate || 0);
 const markedUpPrice = originalPrice * (1 + markupPercentage);
 const option = document.createElement('option');
 option.value = service.service;
 option.text = `${service.name} ($${markedUpPrice.toFixed(2)}/1000, Min: ${service.min}, Max: ${service.max})`;
 serviceSelect.appendChild(option);
 });
 }
 selectedService = null;
 calculateTotal();
 }

 function calculateTotal() {
 console.log('Calculating total - Service:', selectedService, 'Quantity:', quantityInput.value);
 if (selectedService && quantityInput.value) {
 const quantity = parseInt(quantityInput.value);
 const ratePerThousand = parseFloat(selectedService.rate || 0) * (1 + markupPercentage);
 const total = (ratePerThousand / 1000) * quantity;

 if (isNaN(quantity) || quantity <= 0) {
 totalPriceDiv.textContent = 'Enter a valid quantity';
 totalPriceDiv.classList.add('text-danger');
 } else if (quantity < parseInt(selectedService.min)) {
 totalPriceDiv.textContent = `Min quantity: ${selectedService.min}`;
 totalPriceDiv.classList.add('text-danger');
 } else if (quantity > parseInt(selectedService.max)) {
 totalPriceDiv.textContent = `Max quantity: ${selectedService.max}`;
 totalPriceDiv.classList.add('text-danger');
 } else {
 totalPriceDiv.textContent = `$${total.toFixed(2)}`;
 totalPriceDiv.classList.remove('text-danger');
 }
 } else {
 totalPriceDiv.textContent = '$0.00';
 totalPriceDiv.classList.remove('text-danger');
 }
 }

 categorySelect.addEventListener('change', function() {
 const category = this.value;
 console.log('Category changed to:', category);
 if (category) {
 populateServices(category);
 } else {
 serviceSelect.innerHTML = '<option value="">Select a service</option>';
 selectedService = null;
 totalPriceDiv.textContent = '$0.00';
 }
 });

 serviceSelect.addEventListener('change', function() {
 const serviceId = this.value;
 selectedService = services.find(service => String(service.service) === String(serviceId)) || null;
 console.log('Selected service:', selectedService);
 calculateTotal();
 });

 quantityInput.addEventListener('input', function() {
 console.log('Quantity changed to:', this.value);
 calculateTotal();
 });

 // Initial setup
 if (categorySelect.value) {
 populateServices(categorySelect.value);
 } else {
 console.log('No initial category selected');
 }
});
</script>
@endsection