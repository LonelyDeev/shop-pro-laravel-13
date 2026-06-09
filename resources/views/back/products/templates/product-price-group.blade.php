<script id="product-template" type="template/html">
    <div class="row align-items-center order-single-product">
        <div class="col-md-2">
            <img class="w-50" src="<%= product.image %>" alt="<%= product.title %>">
        </div>
        <div class="col-md-8">
            <div class="mb-1">
                <small><%= product.title %></small>
            </div>

        </div>

        <input type="hidden" name="products[]" value="<%= product.id %>"/>

        <div class="col-md-2">
            <button class="btn btn-outline-danger delete-product-btn"><i class="feather icon-trash"></i></button>
        </div>
        <hr class="w-100">
    </div>
</script>
