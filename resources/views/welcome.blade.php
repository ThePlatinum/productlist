<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Products in Json</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

    <!-- Styles -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.1/js/bootstrap.min.js" integrity="sha512-fHY2UiQlipUq0dEabSM4s+phmn+bcxSYzXP4vAXItBvBHU7zAM/mkhCZjtBEIJexhOMzZbgFlPLuErlJF2b+0g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.1/css/bootstrap.min.css" integrity="sha512-Z/def5z5u2aR89OuzYcxmDJ0Bnd5V1cKqBEbvLOiUNWdg9PQeXVvXLI90SE4QOHGlfLqUnDNVAYyZi8UwUTmWQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body class="antialiased">
    <div class="relative sm:flex sm:justify-center sm:items-center min-h-screen bg-dots-darker bg-center bg-gray-100 dark:bg-dots-lighter dark:bg-gray-900 selection:bg-red-500 selection:text-white">
        @if (Route::has('login'))
        <div class="sm:fixed sm:top-0 sm:right-0 p-6 text-right z-10">
            @auth
            <a href="{{ url('/home') }}" class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Home</a>
            @else
            <a href="{{ route('login') }}" class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Log in</a>

            @if (Route::has('register'))
            <a href="{{ route('register') }}" class="ml-4 font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Register</a>
            @endif
            @endauth
        </div>
        @endif

        <div class="container my-4">
            <!-- Form -->
            <h1>Product Management</h1>

            <form id="productForm" class="card card-body mb-4">
                @csrf

                <h2 class="h4">New Product</h2>

                <div class="form-group mt-3">
                    <label for="name">Product Name:</label>
                    <input type="text" class="form-control" name="name" id="name" required>
                </div>

                <div class="form-group mt-3">
                    <label for="quantity">Quantity in Stock:</label>
                    <input type="number" class="form-control" name="quantity" id="quantity" required>
                </div>

                <div class="form-group my-3">
                    <label for="price">Price per Item:</label>
                    <input type="number" class="form-control" name="price" id="price" required>
                </div>

                <p class="text-center text-danger" id="formError"></p>

                <button class="btn btn-primary">Add Product</button>
            </form>

            <!-- Data List -->
            <div class="table-responsive">
                <table class="table caption-top">
                    <caption>List of Products</caption>
                    <thead>
                        <tr>
                            <th scope="col">Product Name</th>
                            <th scope="col">Quantity in Stock</th>
                            <th scope="col">Price per Item</th>
                            <th scope="col">Datetime Submitted</th>
                            <th scope="col">Total Value Number</th>
                            <th scope="col">Edit</th>
                        </tr>
                    </thead>
                    <tbody id="productTableBody"></tbody>
                </table>
            </div>

            <p>Total Value: <span id="totalValue">0</span></p>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-2.2.4.js"></script>
    <script>
        $(document).ready(function() {

            const productTableBody = $('#productTableBody');
            const totalValueElement = $('#totalValue');
            const formErrorElement = $('#formError')

            function formatDate(dateString) {
                const options = {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: 'numeric',
                    minute: 'numeric'
                };
                
                return new Date(dateString).toLocaleDateString(undefined, options);
            }

            function addNewRow(product) {
                const totalValue = (product.quantity * product.price).toFixed();

                const row = `
                    <tr key="${product.id}" product-data="${product}">
                        <td>${product.name}</td>
                        <td>${product.quantity}</td>
                        <td>${product.price}</td>
                        <td>${formatDate(product.created_at)}</td>
                        <td>${totalValue}</td>
                        <td class='d-flex gap-2'>
                            <button class="btn btn-secondary edit_product_btn" data-index="${product.id}">Edit</button>
                            <button class="btn btn-danger delete_product_btn" data-index="${product.id}">Delete</button>
                        </td>
                    </tr>`;

                productTableBody.append(row);

                return totalValue;
            }

            function getProduct() {
                $.ajax({
                    url: "{{ route('product.index') }}",
                    method: 'GET',
                    success: (res) => {
                        productTableBody.empty();

                        let totalValue = 0;

                        res.forEach((product) => {
                            const productTotalValue = addNewRow(product[0])

                            totalValue += parseFloat(productTotalValue);
                        });

                        totalValueElement.text(totalValue);
                    }
                });
            }

            /**
             * Add new product
             */
            $('#productForm').submit(function(event) {
                event.preventDefault();

                const productName = $('#name').val();
                const quantity = $('#quantity').val();
                const price = $('#price').val();

                // Clear error message
                formErrorElement.text('');

                $.ajax({
                    url: "{{route('product.store')}}",
                    method: 'POST',
                    data: {
                        _token: '{{csrf_token()}}',
                        name: productName,
                        quantity: quantity,
                        price: price
                    },
                    success: (res) => {
                        if (res.status) {
                            $('#name').val('');
                            $('#quantity').val('');
                            $('#price').val('');

                            const productTotalValue = addNewRow(res.data)

                            totalValueElement.text(parseFloat(productTotalValue) + parseFloat(totalValueElement.text()))
                        } else {
                            formErrorElement.text(res.message);
                        }
                    },
                    error: (err) => {
                        formErrorElement.text('An error occurred while adding the product.');
                    }
                });
            });

            /**
             * Edit product
             */
            function editProduct(index) {}

            /**
             * Delete product
             */
            $(document).on('click', '.delete_product_btn', function() {
                let productId = $(this).attr('data-index') ?? 0;
                console.log('productId: ', productId);

                $.ajax({
                    url: `/product/${productId}`,
                    method: 'DELETE',
                    data: {
                        _token: '{{csrf_token()}}'
                    },
                    success: (res) => {
                        if (res.status) {
                            $(this).closest('tr').remove();

                            const deletedValue = $(this).closest('tr').find('td:eq(4)').text();

                            totalValueElement.text(parseFloat(totalValueElement.text()) - parseFloat(deletedValue));
                        } else {
                            formErrorElement.text(res.message);
                        }
                    },
                    error: (err) => {
                        formErrorElement.text('An error occurred while deleting the product.');
                    }
                });
            });

            getProduct();
        });
    </script>
</body>

</html>
