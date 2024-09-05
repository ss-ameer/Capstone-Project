<!-- config-script.php -->
<script>

$(document).ready(function(){

    // popover
    const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
    const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl));
    // const popover = new bootstrap.Popover('.popover-dismiss', { trigger: 'focus' });

    // stocks
    function itemAdd() {
        var data = {
            item_name: $('#item-add_name').val(),
            item_category: $('#item-add_category').val(),
            item_uom: $('#item-add_uom').val(),
            item_price: $('#item-add_price').val(),
            item_desc: $('#item-add_desc').val(),
            action: 'item add'
        }

        $.ajax({
            url: '../configs/config-function.php',
            type: 'POST',
            data: data,
            success: function(response) {
                console.log(response);
                if (response !== 'success') {
                    console.log('error');
                } else {
                    $('#master-stock-preview').load(' #master-stock-preview');
                    $('#item-add-form')[0].reset();
                    alert(data['item_name'] + ' successfully added.');
                };
            }
        })
    }

    function stockAdd() {
        var data = {
            item_id: $('#stock-add_id').val(),
            qty: $('#stock-add_qty').val(),
            action: 'stock add'
        }

        $.ajax({
            url: '../configs/config-function.php',
            type: 'POST',
            data: data,
            success: function(response) {
                console.log(response);
                if (response == 'success') {

                    // $('#master-stock-preview').reload();
                    // location.reload();
                }
            }
        })
    }

    function stockGet() {
        var data = {
            action: 'get stocks'
        }

        $.ajax({
            url: '../configs/config-function.php',
            type: 'POST',
            data: data,
            success: function(response) {
                console.log(response);
                // if (response.startsWith('success:')) {
                //     var stock = JSON.parse(response.slice(8));
                //     $('#stock-preview').html(stockTable(stock));
                // }
            }
        })
    }

    function stockDelete(id) {
        if (confirm('Confirm to Delete this stock.')) {
            var data = {
                stock_id: id,
                action: 'stock delete'
            }
            $.ajax({
                url: '../configs/config-function.php',
                type: 'POST',
                data: data,
                success: function(response) {
                    
                    if (response =='success'){
                        $('#master-stock-preview').find(`[data-id_item='${id}']`).remove();
                        console.log('Item Delete: \nSUCCESS \n - item id:', data['stock_id']);
                    } else {
                        alert('Failed to delete item. \n (item id:', data['stock_id']);
                        console.log('Item Delete: \nFAILED \n - item id:', data['stock_id']);
                    }
                }
            });
        }
    }

    function stockSelect(id) {
        var data = {
            stock_id: id,
            action: 'stock select'
        };

        $.ajax({
            url: '../configs/config-function.php',
            type: 'POST',
            data: data,
            success: function(response) {
                console.log('response: ' + response);
                console.log('selected:' , data['stock_id']);
            }
        });
    }

// navigation 
    function goToIndex() {
        window.location.href = '../index.php';
    }

    function sidenavSelect(name) {
        var data = {    
            action: 'sidenav select',
            selected: name
        };

        $.ajax({
            url: '../configs/config-function.php',
            type: 'POST',
            data: data,
            success: function(response) {
                console.log('response: ' + response);
                console.log('selected:' , data['selected']);
                if (response == 'success') {
                    console.log('action: redirecting');
                    goToIndex();
                } else { console.log('action: not redirecting'); }
            }
        })
    }

// account functions
    function selectAccount(id) {
        var data = {
            account_id: id,
            action: 'select account'
        }
        $.ajax({
            url: '../configs/config-function.php',
            type: 'POST',
            data: data,
            success: function(response) {
                console.log(data['account_id']);
            }
        });
    }

    function deleteAccount(id) {
        if (confirm('Confirm to Delete this account.')) {
            var data = {
                account_id: id,
                action: 'delete account'
            }
            $.ajax({
                url: '../configs/config-function.php',
                type: 'POST',
                data: data,
                success: function(response) {
                    console.log(response);
                    
                    if (response == 'success'){
                        $('#table-officers').find(`[data-id_officer='${id}']`).remove();
                    } else {
                        alert('Failed to delete account.');
                    }
                }
            });
        }
    }

    // account
    function register(){
        var data = {
            name: $('#register-name').val(),
            password: $('#register-password').val(),
            password_rep: $('#register-password_rep').val(),
            acc_type: $('#register-check').prop('checked'),
            action: 'register'
        };

        $.ajax({
            url: '../configs/config-function.php',
            type: 'POST',
            data: data,
            success: function(response) {
                console.log("Success" + response);
                $('#feedback').html(response);
            }
        })
    }

    function login() {
        var data = {
            name: $('#login-name').val(),
            password: $('#login-password').val(),
            action: 'login'
        };

        $.ajax({
            url: '../configs/config-function.php',
            type: 'POST',
            data: data,
            success: function(response) {
                $('#feedback').html(response);
                if(response.includes('success')){
                    $('#feedback').html(response);
                    goToIndex(); // Redirect to the login page
                }
            }
        })
    }

    function logout() {
        $.ajax({
            url: '../configs/config-function.php',
            type: 'POST',
            data: { action: 'logout' },
            success: function(response) {
                console.log("Success" + response);
                $('#feedback').html(response);
                goToIndex(); // Redirect to the login page
            }
        })
    }

    $('#register-form').submit(function(event){
        event.preventDefault();
        register();
    });

    $('#login-form').submit(function(event){
        event.preventDefault();
        login();
    });

    $('#logout-button').click(function(event){
        event.preventDefault();
        logout();
    })

    // sidenav

    $('#side-master').click(function(event){
        event.preventDefault();
        sidenavSelect('master');
    })

    $('#side-dashboard').click(function(event){
        event.preventDefault();
        sidenavSelect('dashboard');
    })

    $('#side-stock').click(function(event){
        event.preventDefault();
        sidenavSelect('stock');
    })

    $('#side-orders').click(function(event){
        event.preventDefault();
        sidenavSelect('orders');
    })

    // officers table
    $('#table-officers').on('click', '.officer-account', function(event){
        event.preventDefault();

        var classes = 'table-active';

        $('.officer-account').removeClass(classes);
        $('.officer-account .btn').hide();

        $(this).addClass(classes);
        $(this).find('.btn').show();

        console.log($(this).attr('class'));

        selectAccount($(this).data('id_officer'));
    })

    // delete account
    $('#table-officers').on('click', '.delete-account', function(event){
        // event.stopPropagation();
        var accountId = $(this).closest('.officer-account').data('id_officer');
        deleteAccount(accountId);
    })

    // stocks
    $('#item-add-form').submit(function(event){
        event.preventDefault();
        itemAdd();
    });

    $('#stock-delete').on('click', function(event){
        event.preventDefault();
        var stockId = $('#master-stock-preview tr.table-active').data('id_item');
        stockDelete(stockId);
    });

    $('#stock-add').submit(function(event){ 
        event.preventDefault();
        stockAdd();
    })

    $(document).on('click', '#master-stock-preview tbody tr', function(event){
        event.preventDefault();
        
        var classes = 'table-active';

        $('.item').removeClass(classes);
        $(this).addClass(classes);

        stockSelect($(this).data('id_item'));

        var id = $(this).find('td').eq(0).text();
        var name = $(this).find('td').eq(1).text();
        var category = $(this).find('td').eq(2).text();
        var price = $(this).find('td').eq(3).text();
        var description = $(this).find('td').eq(5).text();

        $('#item-edit_id').val(id);
        $('#item-edit_name').val(name);
        $('#item-edit_category').val(category);
        $('#item-edit_price').val(price);
        $('#item-edit_desc').val(description);
    })

    $('#stock-edit-form-submit').on('click', function(){
        $('#stock-edit-form').submit();
    })

    $('#stock-edit-form').submit(function(event){
        event.preventDefault();

        var data = {
            stock_id: $('#item-edit_id').val(),
            name: $('#item-edit_name').val(),
            category: $('#item-edit_category').val(),
            uom: $('#item-edit_uom').val(),
            price: $('#item-edit_price').val(),
            description: $('#item-edit_desc').val(),
            action: 'stock edit'
        };

        $.ajax({
            url: '../configs/config-function.php',
            type: 'POST',
            data: data,
            success: function(response) {
                if(response == 'success') {
                alert('Item updated successfully.');
                location.reload(); // Reload to reflect changes
                } else {
                    alert('Failed to update item. Please try again.');
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
            }
        });
        
    })

    $("#item-search").on("keyup", function() {
        let query = $(this).val();
        let data = {
            query: query,
            action: 'item search'
        };

        if (query.length >= 1) {
            $.ajax({
                url: "../configs/config-function.php",
                type: "POST",
                data: data,
                success: function(data) {
                    let items = JSON.parse(data);
                    let suggestions = '';
                    items.forEach(function(item) {
                        suggestions += '<li class="list-group-item suggestion-item" data-id="' + item.item_id + '" data-name="' + item.item_name + '" data-uom="' + item.unit_of_measure + '" data-price="' + item.price + '" data-description="' + item.description + '" ><span class="badge bg-dark">' + item.item_id + '</span> ' + item.item_name + '</li>';
                    });
                    $("#item-suggestions").html(suggestions).show();
                    $('#item-suggestions li:gt(4)').remove();
                }
            });
        } else {
            $("#item-suggestions").hide();
        }
    });

    $('#order-form').submit(function(event) {
        event.preventDefault();

        // var formData = $(this).serialize();
        var orderItems = [];

        $('#order-items-table tbody tr').each(function() {
            var row = $(this);
            var item = {
                item_id: row.find('.item-id').text(),
                quantity: row.find('.item-qty').val(),
                price: row.find('.item-price').text(),
                total: row.find('.item-total').text()
            };

            orderItems.push(item);

        });

        var orderData = {
            client_name: $('#order-form-name').val(),
            client_number: $('#order-form-number').val(),
            client_email: $('#order-form-email').val(),
            address: {
                city: $('#order-form-address_city').val(),
                barangay: $('#order-form-address_brgy').val(),
                street: $('#order-form-address_street').val(),
                number: $('#order-form-address_number').val(),
            },
            items: orderItems,
            total_qty: parseInt($('#order-items-total_qty-input').val()),
            total_amount: parseFloat($('#order-items-total_price-input').val()),
            action: 'create order'
        }

        $.ajax ({
            url: '../configs/config-function.php',
            type: 'POST',
            data: orderData,
            success: function(response) {
                console.log(response);
            },
            error: function() {
                alert('An error occurred. Please try again.');
            }
        });
    });

    $('#order-form-name').on('input', function() {
        var query = $(this).val();
        var data = {
            query: query,
            action: 'client search'
        };

        if (query.length > 2) {
            $.ajax({
                url: '../configs/config-function.php',
                type: 'POST',
                data: data,
                success: function(response) {
                    console.log(response);
                    var suggestions = JSON.parse(response);
                    var suggestionsList = $('#order-form-client-suggestions');

                    suggestionsList.empty();

                    if (suggestions.length > 0) {
                        suggestions.forEach(function(suggestion) {
                            suggestionsList.append('<li class="list-group-item client-suggestion-item" data-client-id="' + suggestion.client_id + '">' + suggestion.name + '</li>');
                        });
                        suggestionsList.show();
                    } else {
                        suggestionsList.hide();
                    }
                }
            });
        } else {
            $('#order-form-client-suggestions').hide();
        }
    });

    $(document).on('click', '.client-suggestion-item', function() {
        var client_id = $(this).data('client-id');
        var data = {
            client_id: client_id,
            action: 'get client info'
        };

        // $('#order-form-name').val(selectedName);
        if (client_id) {
            $.ajax({
                type: 'POST',
                url: '../configs/config-function.php',
                data: data,
                dataType: 'json',
                success: function(response) {
                    console.log(response.success);
                    if (response.success) {
                        $('#order-form-name').val(response.client_name);
                        $('#order-form-number').val(response.phone);
                        $('#order-form-email').val(response.email);
                        $('#order-form-address_city').val(response.address.city);
                        $('#order-form-address_brgy').val(response.address.barangay);
                        $('#order-form-address_street').val(response.address.street);
                        $('#order-form-address_number').val(response.address.house_number);
                        $('#order-form-client-suggestions').hide();
                        // console.log('success')
                        console.log(response.client_name);
                        console.log(response.phone);
                        console.log(response.email);
                        console.log(response.address.street);

                    } else {
                        console.error('No client info found.');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error: ', status, error);  // Log the error
                    console.log(xhr.responseText);  // Log the server response for debugging
                    console.log('failed')
                }
            })
        } else { console.log('error') };
    });

    $(document).on('click', '#item-suggestions li', function(){
        var itemId = $(this).data('id');
        var itemName = $(this).data('name');
        var itemUnit = $(this).data('uom');
        var itemDescription = $(this).data('description');
        var itemPrice = $(this).data('price');
        
        console.log('clicked');
        console.log('item id: ' + itemId);
        addItemToTable(itemId, itemName, itemUnit, itemPrice);
        $('#item-search').val('');
        $('#item-suggestions').hide();
        
    })

    $(document).on('click', '#order-item-remove', function() {
        $(this).closest('tr').remove();
        calculateOrderSummary();
    })

    $(document).on('click', '#order-items-table tbody tr .inc-qty', function(){
        var $input = $(this).closest('.input-group').find('.item-qty');
        var qty = parseInt($input.val());
        console.log('clicked: inc qty');
        $input.val(qty + 1);
        updateOrderTable($(this).closest('tr'));
    });
    
    $(document).on('click', '#order-items-table tbody tr .dec-qty', function(){
        var $input = $(this).closest('.input-group').find('.item-qty');
        var qty = parseInt($input.val());
        console.log('clicked: dec qty');
        if(qty > 1) {
            $input.val(qty - 1);
            updateOrderTable($(this).closest('tr'));
        }
    });

    $(document).on('input', '#order-items-table tbody tr .item-qty', function(){
        console.log('input: item qty');
        var $row = $(this).closest('tr');
        updateOrderTable($row);
    });

    function updateOrderTable($row) {
        if(!$row) {
            $('#order-items-table tbody tr').each(function() {
                var $thisRow = $(this);
                var qty = parseInt($thisRow.find('.item-qty').val());
                var price = parseFloat($thisRow.find('.item-price').text());
                var total = qty * price;
                $thisRow.find('.item-total').text(total.toFixed(2));
            });
        } else {
            var qty = parseInt($row.find('.item-qty').val());
            var price = parseFloat($row.find('.item-price').text());
            var total = qty * price;
            $row.find('.item-total').text(total.toFixed(2));
        }
        calculateOrderSummary();
    }

    function calculateOrderSummary() {
        var totalQty = 0;
        var totalPrice = 0;
        
        $('#order-items-table tbody tr').each(function() {
            var qty = parseInt($(this).find('.item-qty').val());
            var total = parseFloat($(this).find('.item-total').text());

            totalQty += qty;
            totalPrice += total;
        });

        
        $('#order-items-total_qty span').text(`${totalQty}`);
        $('#order-items-total_price span').text(`${totalPrice}`);

        $('#order-items-total_qty-input').val(totalQty);
        $('#order-items-total_price-input').val(totalPrice.toFixed(2));

        console.log('Total QTY:' + totalQty);
        console.log('Total Price:' + totalPrice);

    }

    function addItemToTable(id, name, unit, price) {
        var newRow = 
                    '<tr class="">' +
                        '<td class="item-id">' + id + '</td>' +
                        '<td>' + name + '</td>' +
                        '<td>' + 
                            '<div class="input-group input-group-sm mh-100">' + 
                                '<button class="btn btn-outline-danger input-group-text dec-qty">-</button>' + 
                                '<input type="text" class="form-control item-qty text-center" value="1" inputmode="numeric">' + 
                                '<button class="btn btn-outline-success input-group-text inc-qty">+</button>' + 
                            '</div>' + 
                        '</td>' +
                        '<td class="item-price">' + price + '</td>' +
                        '<td class="item-total">' + price + '</td>' +
                        '<td class="text-center"><i class="bi bi-x-circle fs-6" id="order-item-remove"></i></td>'
                    '</tr>';
        
        $('#order-items-table tbody').append(newRow);
        updateOrderTable();
    }

    $('#preview-order-btn').on('click', function() {
        $('#preview-client-name').text('Name: ' + $('#order-form-name').val());
        $('#preview-client-address').text('Address: ' + 
            $('#order-form-address_number').val() + ', ' +
            $('#order-form-address_street').val() + ' Street' + ', ' +
            $('#order-form-address_brgy').val() + ', ' +
            $('#order-form-address_city').val() 
        );
        $('#preview-client-phone').text('Phone: ' + $('#order-form-number').val());
        $('#preview-client-email').text('Email: ' + $('#order-form-email').val());

        let itemsHtml = '';
        $('#order-items-table tbody tr').each(function() {
            let itemId = $(this).find('td').eq(0).text();
            let itemName = $(this).find('td').eq(1).text();
            let itemQty = $(this).find('.item-qty').val();
            let itemPrice = $(this).find('td').eq(3).text();
            let itemTotalPrice = $(this).find('td').eq(4).text();

            itemsHtml += 
            `<tr>
                <td>${itemId}</td>
                <td>${itemName}</td>
                <td>${itemQty}</td>
                <td>${itemPrice}</td>
                <td>${itemTotalPrice}</td>
            </tr>`;
        });
        $('#preview-order-items').html(itemsHtml);

        $('#preview-total-qty').text('Total Quantity: ' + $('#order-items-total_qty span').text());
        $('#preview-total-price').text('Total Price: ' + $('#order-items-total_price span').text());
    });
});

</script>