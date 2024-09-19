<!-- comp-content-order-view.php -->
<?php 

?>
<div id="dispatch-order-view" class="container p-0 border shadow-sm rounded position-relative" style="max-height: 400px; height: 400px; overflow-y: auto;">
    
    <!-- header -->
    <div class="sticky-top bg-light p-2 border-bottom">
        <div class="d-flex justify-content-between">
            <span class="lead">Order ID: <span class="order-id"></span></span>
            <button type="button" class="btn-close" aria-label="Close"></button>
        </div>
    </div>
    
    <!-- content -->
    <div class="p-3">
        <div class="card mb-2" id="order-display-client">
            <div class="card-header">
                <h6 class="card-title">Client</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-1">
                    <span>name : </span><span class="name"></span>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span>number : </span><span class="number"></span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>email : </span><span class="email"></span>
                </div>
            </div>
        </div>

        <div class="card mb-2" id="order-display-location">
            <div class="card-header">
                <h6 class="card-title">Location</h6>
            </div>
            <div class="card-body">                                    
                <div class="d-flex justify-content-between">
                    <span class="location"></span>
                </div>
            </div>
        </div>

        <div class="card" id="order-display-items">
            <div class="card-header">
                <h6 class="card-title">Order Items</h6>
            </div>
            <div class="card-body">

                <ul class="list-group mb-2 pending">
                    <li class="list-group-item list-group-item-secondary text-center">
                        <span>Pending</span>
                    </li>
                    <!-- order items will be dynamically generated. -->
                </ul>
                
                <ul class="list-group mb-2 in-queue">
                    <li class="list-group-item list-group-item-primary text-center">
                        <span>In-queue</span>
                    </li>
                </ul>

                <ul class="list-group mb-2 in-progress">
                    <li class="list-group-item list-group-item-info text-center">
                        <span>In-progress</span>
                    </li>
                </ul>

                <ul class="list-group mb-2 successful">
                    <li class="list-group-item list-group-item-success text-center">
                        <span>Successful</span>
                    </li>
                </ul>

                <ul class="list-group canceled">
                    <li class="list-group-item list-group-item-dark text-center">
                        <span>Canceled</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

</div>