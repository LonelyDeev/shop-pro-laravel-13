<script id="product-template" type="template/html">
    <div class="row align-items-center order-single-product">
        <div class="col-md-2">
            <img class="w-100" src="<%= product.image %>" alt="<%= product.title %>">
        </div>
        <div class="col-md-6">
            <div class="mb-1">
                <strong><%= product.title %></strong>
            </div>
            <% if (product.prices[0].attributes.length) { %>
                <div class="mb-1">
                    <select class="form-control price-select">
                        <% product.prices.forEach(function(price){ %>

                            <option value="<%= price.id %>" data-price="<%= JSON.stringify(price) %>">
                                <% let attcount = 0 %>
                                <% price.attributes.forEach(function(attribute){ %>
                                    <%= attribute.group.name %> : <%= attribute.name %>
                                    <% if (++attcount < price.attributes.length) { %>
                                        ,
                                    <% } %>
                                <% }); %>
                            </option>
                        <% }); %>
                    </select>
                </div>
            <% } %>
            <strong class="text-success"><span class="sale-price"><%= number_format(product.price) %></span> تومان</strong>

            <del class="text-danger regular-price-container <% if (!product.regular_price) { %> d-none <% } %>"><span class="regular-price"><%= number_format(product.regular_price) %></span> تومان</del>

            <input class="selected-price" name="products[<%= productsCount %>][price_id]" type="hidden" value="<%= product.prices[0].id %>">
            <input name="products[<%= productsCount %>][id]" type="hidden" value="<%= product.id %>">
        </div>
        <div class="col-md-2">
            <div class="form-group">
                <label for="">تعداد</label>
                <input max="<%= product.prices[0].cart_max  %>" type="number" name="products[<%= productsCount %>][quantity]" class="form-control product-quantity" value="1">
            </div>
        </div>
        <div class="col-md-2">
            <button class="btn btn-outline-danger delete-product-btn"><i class="feather icon-trash"></i></button>
        </div>
        <hr class="w-100">
    </div>
</script>
