<?php 
// config-function.php

    // ini_set('display_errors', 1);
    // ini_set('display_startup_errors', 1);
    // error_reporting(E_ALL);

    session_start();

    if (isset($_SESSION['user_info'])) {
        $officer_logged_in_id = $_SESSION['user_info']['id'];
        $officer_logged_in_name = $_SESSION['user_info']['name'];
        $officer_logged_in_role = $_SESSION['user_info']['role'];
    };

    function getCurrentOfficer($return_type) {
        if (isset($_SESSION['user_info'])) {
            return $return_type == 'id' ? $_SESSION['user_info']['id'] : $_SESSION['user_info']['name'];
        }

        return false;
    };

// database initialization

    $servername = 'localhost';
    $username = 'root';
    $password = '';
    $dbname = 'capstone_project_db';
    $dbstatus = false;

    try {

        $conn = new mysqli($servername, $username, $password, $dbname);
        $dbstatus = true;

    } catch (mysqli_sql_exception) {
        exit();
    }

    define('ROOT', 'http://localhost/_capstone-project/');
    define('PATH', $_SERVER['DOCUMENT_ROOT'] . '/_capstone-project/');

    if (isset($_POST['action'])) {

        if($dbstatus == true) {
            switch ($_POST['action']) {
                case 'register':
                    register();
                    break;

                case 'login':
                    login();
                    break;

                case 'logout':
                    logout();
                    break;

                case 'select account':
                    selectAccount($_POST['account_id']);
                    break;

                case 'delete account':
                    deleteAccount($_POST['account_id']);
                    break;

                case 'sidenav select':
                    sidenavSelect($_POST['selected']);
                    break;

                case 'get stocks':
                    getItems();
                    break;

                case 'item add':
                    addItem();
                    break;

                case 'stock add':
                    addStock();
                    break;

                case 'stock select':
                    selectStock($_POST['stock_id']);
                    break;

                case 'stock delete':
                    deleteStock($_POST['stock_id']);
                    break;

                case 'stock edit':
                    editStock();
                    break;

                case 'item search':
                    itemSearch();
                    break;

                case 'create order':
                    saveOrder();
                    break;

                case 'client search':
                    searchClients();
                    break;

                case 'get client info':
                    getClientInfo();
                    break;

                case 'get units info':
                    $units = dbGetTableData('truck_types');
                    echo json_encode($units);
                    break;

                case 'add unit':
                    addUnit();
                    break;

                case 'add unit_type':
                    addUnitType();
                    break;

                case 'add driver':
                    addDriver();
                    break;
                
                case 'dispatch update order view':
                    $order_id = $_POST['order_id'];
                    $order_data = getOrderData($order_id);
                    echo json_encode($order_data);
                    break;
                
                case 'get dispatch form options':
                    $unit_type_id = $_POST['unit_type_id'];
                    $units = getUnitsFiltered($unit_type_id);
                    $drivers = getDrivers();

                    $options = [
                        'units' => $units,
                        'drivers' => $drivers
                    ];

                    echo json_encode($options);
                    break;

                case 'submit dispatch form':

                    $unit_id = $_POST['unit_id'];
                    $operator_id = $_POST['operator_id'];
                    $order_item_id = $_POST['order_item_id'];
                    $officer_id = $officer_logged_in_id;

                    $success = addDispatchRecord($order_item_id, $unit_id, $operator_id, $officer_id);

                    if ($success) {
                        echo json_encode(['success' => true]);
                    } else {
                        echo json_encode(['success' => false, 'error' => 'Failed to add dispatch record']);
                    }

                    break;

                case 'get dispatch pending orders':
                    
                    error_log("Action received: get dispatch pending orders");
                    echo getDispatchPendingOrdersHtml();
                    break;

                case 'update dispatch table':
                    
                    echo json_encode(getDispatchRecords());
                    break;

                case 'update dispatch status':
                    $dispatch_id = $_POST['dispatch_id'];
                    $new_status = $_POST['new_status'];
                    $failed_reason = isset($_POST['failed_reason']) ? $_POST['failed_reason'] : null;
                    $failed_type = isset($_POST['failed_type']) ? $_POST['failed_type'] : null;

                    $results = updateDispatchStatus($dispatch_id, $new_status, $failed_reason, $failed_type);
                
                    echo json_encode($results);
                    break;

                case 'print dispatch slip':
                    
                    $entity_type = $_POST['entity_type'];
                    $entity_id = $_POST['entity_id'];
                    $event_type = $_POST['event_type'];
                    $event_description = $_POST['event_description'];
                    $user_id = getCurrentOfficer('id');

                    $log_data = [
                        'entity_type' => $entity_type,
                        'entity_id' => $entity_id,
                        'event_type' => $event_type,
                        'event_description' => $event_description,
                        'user_id' => $user_id
                    ];
                
                    if (!logEvent($log_data)) {
                        
                        echo json_encode(['success' => false]);

                    } else {
                        echo json_encode(['success' => true]);
                    }
                    
                    break;

                case 'delete':
                    $value = $_POST['id'];
                    $table = $_POST['table'];
                    $id_column = $_POST['id_column'];
                    $result = dbDeleteRow($table, $id_column, $value);

                    echo json_encode($result);

                    if($result['success']) {
                        $log_data = [
                            'entity_type' => $table,
                            'entity_id' => $value,
                            'event_type' => 'Delete',
                            'event_description' => 'Deleted '. $table.'with ID '. $value,
                            'user_id' => getCurrentOfficer('id')
                        ];

                        logEvent($log_data);
                    }
                    break;

                case 'check dependencies':
                    $id = $_POST['id'];
                    $dependency_checks = $_POST['dependency_checks']; 
                    
                    $result = dbCheckDependencies($id, $dependency_checks);
                    
                    echo json_encode($result);
                    break;

                case 'reassign':
                    $id = $_POST['id'];
                    $reassign_value = $_POST['reassign_value'];
                    $dependency_checks = $_POST['dependency_checks'];

                    $results = [];

                    foreach ($dependency_checks as $dependency) {
                        $dep_table = $dependency['table'];
                        $dep_column = $dependency['column'];

                        $result = reassignDependencies($dep_table, $dep_column, $id, $reassign_value);
                        $results[] = $result;
                    }

                    echo json_encode(['success' => true, 'results' => $results]);
                    break;

                case 'get modal options':
                    $table = $_POST['table'];
                    $columns = $_POST['columns'];
                    $display = $_POST['display'];

                    $stmt = $conn->prepare("SELECT `$columns`, `$display` FROM `$table`");
                    $stmt->execute();
                    echo json_encode($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
                    break;

                case 'edit':
                    $id = $_POST['id'];
                    $table = $_POST['table'];
                    $id_column = $_POST['id-column'];

                    $fields = [];

                    foreach ($_POST as $column => $value) {
                        if ($column != 'id' && $column != 'table' && $column != 'action' && $column != 'id-column') {
                            $fields[] = "`$column` = ?";
                        }
                    }

                    $set_clause = implode(', ', $fields);

                    $sql = "UPDATE `$table` SET $set_clause WHERE `$id_column` = ?";
                    $stmt = $conn->prepare($sql);
                    $types = str_repeat('s', count($fields)) . 'i';
                    $values = array_values(array_diff_key($_POST, array_flip(['id', 'table', 'action', 'id-column'])));
                    $values[] = $id;
                    $stmt -> bind_param($types,...$values);

                    if ($stmt->execute()) {
                        echo json_encode(['success' => true]);
                        $log_data = [
                            'entity_type' => $table,
                            'entity_id' => $id,
                            'event_type' => 'edit',
                            'event_description' => 'Edited ' . $table . ' with ID: ' . $id . '.',
                            'user_id' => getCurrentOfficer('id')
                        ];

                        logEvent($log_data);

                    } else {
                        echo json_encode(['success' => false, 'message' => 'Failed to update record.']);
                    }
                    break;

                case 'table show more':
                    $table = $_POST['table_id']; 
                    $offset = $_POST['offset'];

                    $limit = 10;

                    $total_count = dbGetTableCount($table);

                    $data = dbGetTableData($table, limit: $limit, offset: $offset);

                    $columns = [];

                    switch ($table) {
                        case 'addresses':
                            foreach ($data as &$address){
                                $address['columns'] = [
                                    [
                                        'type' => 'select',
                                        'table' => 'clients',
                                        'columns' => 'client_id',
                                        'display' => 'name',
                                        'data' => ['client_id' => $address['client_id']]
                                    ]
                                ];
                            }
                            break;
                            
                        case 'orders':
                            foreach ($data as &$order){
                                $order['columns'] = [
                                    [
                                        'type' => 'text',
                                        'data' => ['created_at' => $order['created_at']]
                                    ],
                                    [
                                        'type' => 'select',
                                        'table' => 'addresses',
                                        'columns' => 'address_id',
                                        'display' => 'address_id',
                                        'data' => ['address_id' => $order['address_id']]
                                    ],
                                    [
                                        'type' => 'select',
                                        'table' => 'clients',
                                        'columns' => 'client_id',
                                        'display' => 'name',
                                        'data' => ['client_id' => $order['client_id']]
                                    ],
                                    [
                                        'type' => 'text',
                                        'data' => ['total_qty' => $order['total_qty']]
                                    ],
                                    [
                                        'type' => 'text',
                                        'data' => ['total_amount' => $order['total_amount']]
                                    ],
                                    [
                                        'type' => 'select manual',
                                        'options' => ['pending', 'complete', 'canceled'],
                                        'data' => ['status' => $order['status']]
                                    ]
                                ];
                            }
                            break;

                        case 'order_items':
                            foreach ($data as &$order_item){
                                $order_item['columns'] = [
                                    [
                                        'type' => 'select',
                                        'table' => 'orders',
                                        'columns' => 'id',
                                        'display' => 'id',
                                        'data' => ['order_id' => $order_item['order_id']]
                                    ],
                                    [
                                        'type' => 'select',
                                        'table' => 'items',
                                        'columns' => 'item_id',
                                        'display' => 'item_name',
                                        'data' => ['item_id' => $order_item['item_id']]
                                    ],
                                    [
                                        'type' => 'select',
                                        'table' => 'truck_types',
                                        'columns' => 'id',
                                        'display' => 'type_name',
                                        'data' => ['truck_type_id' => $order_item['truck_type_id']]
                                    ],
                                    [
                                        'type' => 'text',
                                        'data' => ['price' => formatPrice($order_item['price'])]
                                    ],
                                    [
                                        'type' => 'text',
                                        'data' => ['item_total' => formatPrice($order_item['item_total'])]
                                    ],
                                    [
                                        'type' => 'select manual',
                                        'options' => ['pending', 'in-queue', 'in-progress', 'failed', 'completed', 'canceled'],
                                        'data' => ['status' => $order_item['status']]
                                    ]
                                ];
                            }
                            break;

                        case 'truck_types':
                            foreach ($data as &$unit_type){
                                $unit_type['columns'] = [
                                    [
                                        'type' => 'text',
                                        'data' => ['type_name' => $unit_type['type_name']]
                                    ],
                                    [
                                        'type' => 'text',
                                        'data' => ['capacity' => $unit_type['capacity']]
                                    ]
                                ];
                            }
                            break;

                        case 'clients':
                            foreach ($data as &$client){
                                $client['columns'] = [
                                    [
                                        'type' => 'text',
                                        'data' => ['name' => $client['name']]
                                    ],
                                ];
                            }
                            break;

                        case 'items':
                            foreach($data as &$item){
                                $item['columns'] = [
                                    [
                                        'type' => 'text',
                                        'data' => ['item_name' => $item['item_name']]
                                    ],
                                    [
                                        'type' => 'text',
                                        'data' => ['description' => $item['description']]
                                    ],
                                    [
                                        'type' => 'text',
                                        'data' => ['category' => $item['category']]
                                    ],
                                    [
                                        'type' => 'text',
                                        'data' => ['density' => $item['density']]
                                    ],
                                    [
                                        'type' => 'text',
                                        'data' => ['price' => $item['price']]
                                    ]
                                ];
                            }
                        
                        case 'dispatch_officers':
                            foreach ($data as &$officer){
                                $officer['columns'] = [
                                    [
                                        'type' => 'text',
                                        'data' => ['name' => $officer['name']] 
                                    ],
                    
                                    [
                                        'type' => 'select manual',
                                        'options' => ['officer', 'master'],
                                        'data' => ['role' => $officer['role']]
                                    ]
                                ];
                            }

                        case 'drivers':
                            foreach ($data as &$driver) {
                                $driver['columns'] = [
                                    [
                                        'type' => 'text',
                                        'data' => ['name' => $operator['name']]
                                    ],
                                    [
                                        'type' => 'text',
                                        'data' => ['license_number' => $operator['license_number']]
                                    ],
                                    [
                                        'type' => 'text',
                                        'data' => ['phone_number' => $operator['phone_number']]
                                    ],
                                    [
                                        'type' => 'select manual',
                                        'options' => ['available', 'on_trip', 'unavailable'], 
                                        'data' => ['status' => $operator['status']]
                                    ]
                                ];
                            }
                            break;

                        case 'trucks':
                            foreach($data as &$truck) {
                                $truck['columns'] = [
                                    [
                                        'type' => 'text',
                                        'data' => ['truck_number' => $unit['truck_number']]
                                    ],
                                    [   
                                        'type' => 'select',
                                        'table' => 'truck_types',
                                        'columns' => 'id',
                                        'display' => 'type_name',
                                        'data' => ['truck_type_id' => $unit['truck_type']]
                                    ],
                                    [
                                        'type' => 'select manual',
                                        'options' => ['available', 'in_use', 'maintenance', 'out_of_service'],
                                        'data' => ['status' => $unit['status']]
                                    ]
                                ];
                            }

                        default:
                            break;
                            
                    }

                    echo json_encode(['data' => $data, 'total_count' => $total_count]);
                    break;
                
                case 'get current officers':
                    $type = $_POST['type'];
                    $current_officer = getCurrentOfficer($type);

                    echo json_encode($current_officer);

                case 'get dispatch count':
                    $in_queue_count = dbGetTableCount('dispatch', "status = 'in-queue'");
                    $in_transit_count = dbGetTableCount('dispatch', "status = 'in-transit'");

                    echo json_encode(['in_queue_count' => $in_queue_count, 'in_transit_count' => $in_transit_count]);
                    break;

                case 'search table rows':
                    $input = $_POST['input'];
                    $table = $_POST['table'];
                    $column = $_POST['column'];

                    $search_data = [
                        'column' => $column,
                        'query' => $input
                    ];

                    $results = dbGetTableData(tableName: $table, search: $search_data);

                    echo json_encode(['results' => $results]);

                    break;

                
                // end of switch
                default:
                    break;

            };
        } 
        else { 
            echo (
                '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <strong>Warning!</strong> Something went wrong with the database connection.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>'
            );
            exit();
        }
    }

    include 'config-cms.php';

    $unit_types = dbGetTableData('truck_types');

    function formatPrice($value) {
        return number_format($value, 2, '.', ',');
    }

    function itemSearch() {
        global $conn;
        $query = $_POST['query'];

        $sql = "SELECT * FROM items WHERE item_name LIKE ?";
        $stmt = $conn -> prepare($sql);
        $search_param = "%" . $query . "%";
        $stmt -> bind_param("s", $search_param);
        $stmt -> execute();
        $result = $stmt -> get_result();

        $items = [];
        while ($row = $result -> fetch_assoc()) {
            $items[] = $row;
        }

        echo json_encode($items);
    }

    function addStock() {
        global $conn;

        $item_id = $_POST['item_id'];
        $qty = $_POST['qty'];

        $stmt = $conn -> prepare ("UPDATE items SET quantity_in_stock = quantity_in_stock +? WHERE item_id =?");
        $stmt -> bind_param("ii", $qty, $item_id);

        if ($stmt -> execute()) {
            echo'success';
        } else  echo 'error';

        $stmt->close();

    }

    function getItems ($limit = 0, $offset = 0) {
        global $conn;

        $stmt = $conn -> prepare("SELECT item_id, item_name, description, category, density, price FROM items");
        $stmt -> execute();
        $result = $stmt -> get_result();

        $items = [];
        while ($row = $result -> fetch_assoc()) {
            $items[] = $row;
        }

        $_SESSION['items'] = $items;
        $stmt->close();
    }

    function addItem() {

        global $conn;

        $user_id = getCurrentOfficer('id');

        $form_data_string = $_POST['form_data'];
        parse_str($form_data_string, $form_data);

        $item_name = $form_data['item_name'];
        $item_category = $form_data['item_category'];
        $item_density = $form_data['item_density'];
        $item_price = $form_data['item_price'];
        $item_description = $form_data['item_description'];

        $stmt = $conn -> prepare ("INSERT INTO items (item_name, category, density, description, price) VALUES (?, ?, ?, ?, ?)");
        $stmt -> bind_param("ssdsd", $item_name, $item_category, $item_density, $item_description, $item_price);

        if($stmt -> execute()){
            
            $log_data = [
                'entity_type' => 'item',
                'entity_id' => $conn->insert_id, 
                'event_type' => 'create',
                'event_description' => 'Item added: ' . $item_name,
                'user_id' => $user_id
            ];

            logEvent($log_data);
            echo'success';
        }

        $stmt->close();
    }

    function editStock() {
        global $conn;

        $stock_id = $_POST['stock_id'];
        $name = $_POST['name'];
        $category = $_POST['category'];
        $uom = $_POST['uom'];
        $price = $_POST['price'];
        $description = $_POST['description'];

        $stmt = $conn->prepare("UPDATE items SET item_name = ?, category = ?, unit_of_measure = ?, price = ?, description = ? WHERE item_id = ?");
        $stmt->bind_param("sssisi", $name, $category, $uom, $price, $description, $stock_id);

        if ($stmt->execute()) {
            echo 'success';
        } else {
            echo 'error';
        }

        $stmt->close();
    }

    function register() {
        global $conn;

        $name = $_POST['name'];
        $username = $_POST['username'];
        $password = $_POST['password'];
        $password_rep = $_POST['password_rep'];
        $acc_type = $_POST['acc_type'] == 'true' ? 0 : 1;

        // check: filled
        if (empty($name) || empty($username) || empty($password) || empty($password_rep)) {
            echo '<div class="alert alert-danger">All fields are required.</div>';
            exit();
        }

        // check: password
        if ($password!== $password_rep) {
            echo '<div class="alert alert-danger">Passwords do not match.</div>';
            exit();
        }

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $acc_type = $acc_type ? 'officer' : 'master';

        $stmt = $conn -> prepare ("INSERT INTO dispatch_officers (name, username, password, role) VALUES (?, ?, ?, ?)");
        $stmt -> bind_param("ssss", $name, $username, $hashed_password, $acc_type);

        $stmt -> execute();

        getAccounts ();

        echo '<div class="alert alert-success">Registered successfully.</div>';
    }

    function getUserInfo($user_id) {

        global $conn;

        $stmt = $conn -> prepare("SELECT * FROM dispatch_officers WHERE id = ?");
        $stmt -> bind_param("i", $user_id);
        $stmt -> execute();
        $stmt -> bind_result($db_id, $db_name, $db_username, $db_password, $db_created_at, $db_updated_at, $db_role);
        $stmt -> fetch();
        $stmt -> close();

        return array(
            'id' => $db_id,
            'name' => $db_name,
            'username' => $db_username,
            'password' => $db_password,
            'role' => $db_role,
            'created_at' => $db_created_at,
            'updated_at' => $db_updated_at
        );
        
    }

    function getAccounts () {
        
        global $conn;

        $stmt = $conn -> prepare("SELECT id, name, role, created_at, updated_at FROM dispatch_officers");
        $stmt -> execute();
        $result = $stmt -> get_result();

        $officers = [];
        while ($row = $result -> fetch_assoc()) {
            $officers[] = $row;
        }

        $_SESSION['officers'] = $officers;

    }

    function login() {

        global $conn;

        $username = $_POST['username'];
        $password = $_POST['password'];

        if (empty($username) || empty($password)) {
            echo '<div class="alert alert-danger">All fields are required.</div>';
            return;
        }

        $stmt = $conn -> prepare("SELECT id, password FROM dispatch_officers WHERE username = ?");
        $stmt -> bind_param("s", $username);
        $stmt -> execute();
        $stmt -> store_result();

        if ($stmt -> num_rows() > 0) {
            $stmt -> bind_result($db_id, $db_hashed_password);
            $stmt -> fetch();
            
            if (password_verify($password, $db_hashed_password)) {
                // session_start();
                $userInfo = getUserInfo($db_id);
                $_SESSION['user_info'] = $userInfo;
                // echo $_SESSION['user_info']['name'];
                echo '<div class="alert alert-success">Login successful.</div>';

                $log_data = [ 
                    'entity_type' => 'dispatch_officers',
                    'entity_id' => $_SESSION['user_info']['id'],
                    'event_type' => 'Login',
                    'event_description' => 'Logged in: ' . $_SESSION['user_info']['name'],
                    'user_id' => $_SESSION['user_info']['id']
                ];

                logEvent($log_data);

            } else { echo '<div class="alert alert-danger">Invalid credentials.</div>';}
        } else { echo '<div class="alert alert-danger">User not found.</div>'; }
    }

    function logout() {
        
        $log_data = [
            'entity_type' => 'dispatch_officers',
            'entity_id' => $_SESSION['user_info']['id'],
            'event_type' => 'Logout',
            'event_description' => 'Logged out: ' . $_SESSION['user_info']['name'],
            'user_id' => $_SESSION['user_info']['id']
        ];

        logEvent($log_data);

        session_start();
        session_unset();
        session_destroy();
        
    }

    function selectAccount($info) {
        // session_start();
        $_SESSION['selected_account'] = $info;

    }

    function deleteStock($id){
        
        global $conn;
        $stmt = $conn -> prepare("DELETE FROM items WHERE item_id = ?");
        $stmt -> bind_param("i", $id);
        
        if ($stmt -> execute()) {
            echo 'success';
        } else  echo 'error';
    }

    function deleteAccount($id) {
        
        global $conn;
        $stmt = $conn -> prepare("DELETE FROM dispatch_officers WHERE id = ?");
        $stmt -> bind_param("i", $id);
        
        if ($stmt -> execute()) {
            echo 'success';
        } else  echo 'error';

    }

    // sidenav
    function sidenavSelect($selected) {

        if ($_SESSION['sidenav_active'] != $selected) {
            $_SESSION['sidenav_active'] = $selected;
            echo'success';
        } else {
            echo 'failed ';
        }
    }

    function selectStock($selected) {
        
            $_SESSION['stock_active'] = $selected;
            echo'success';

    }

    // orders
    function saveOrder() {
        
        global $conn;

        mysqli_begin_transaction($conn);

        try {

            $user_id = getCurrentOfficer('id');

            // client information
            $client_name = $_POST['client_name'];
            $client_number = $_POST['client_number'];
            $client_email = $_POST['client_email'];

            $checkClientQuery = "SELECT client_id FROM clients WHERE name = ?";
            $stmt = $conn -> prepare($checkClientQuery);
            $stmt -> bind_param("s", $client_name);
            $stmt -> execute();
            $stmt -> store_result();

            if ($stmt -> num_rows > 0) {
                $stmt -> bind_result($client_id);
                $stmt -> fetch();

                $log_data = [
                    'entity_type' => 'client',
                    'entity_id' => $client_id,
                    'event_type' => 'reuse',
                    'event_description' => 'Client reused: ' . $client_name,
                    'user_id' => $user_id
                ];

                logEvent($log_data);
                
                // contact information
                // checking mobile number
                $checkContactQuery = "SELECT id FROM contacts WHERE client_id = ? AND contact_type = ? AND contact_value = ?";
                $stmt = $conn -> prepare($checkContactQuery);

                $contactType = 'phone';
                $stmt -> bind_param("isi", $client_id, $contactType, $client_number);
                $stmt -> execute();
                $stmt -> store_result();

                // inserting if number does not exist
                if ($stmt -> num_rows == 0) {
                    $insertContactQuery = "INSERT INTO contacts (client_id, contact_type, contact_value) VALUES (?,?, ?)";
                    $stmt = $conn -> prepare($insertContactQuery);
                    $stmt -> bind_param("isi", $client_id, $contactType, $client_number);
                    $stmt -> execute();
                }

                // checking email address
                $contactType = 'email';
                $stmt -> bind_param("iss", $client_id, $contactType, $client_email);
                $stmt -> execute();
                $stmt -> store_result();

                if ($stmt -> num_rows == 0) {
                    
                    $log_data = [
                        'entity_type' => 'contact',
                        'entity_id' => $conn->insert_id,
                        'event_type' => 'create',
                        'event_description' => 'Email contact created for client: ' . $client_email,
                        'user_id' => $user_id
                    ];

                    logEvent($log_data);

                    $insertContactQuery = $stmt = $conn -> prepare($insertContactQuery);
                    $stmt -> bind_param("iss", $client_id, $contactType, $client_email);
                    $stmt -> execute();
                }

            } else {
                // insert a new client
                $insertClientQuery = "INSERT INTO clients (name) VALUES (?)";
                $stmt = $conn -> prepare($insertClientQuery);
                $stmt -> bind_param("s", $client_name);
                $stmt -> execute();
                $client_id = $conn -> insert_id;

                $log_data = [
                    'entity_type' => 'client',
                    'entity_id' => $client_id,
                    'event_type' => 'create',
                    'event_description' => 'New client created: ' . $client_name,
                    'user_id' => $user_id
                ];

                logEvent($log_data);

                $insertContactQuery = "INSERT INTO contacts (client_id, contact_type, contact_value) VALUES (?, ?, ?)";
                
                // phone number
                $contactType = 'phone';
                $stmt = $conn->prepare($insertContactQuery);
                $stmt->bind_param("iss", $client_id, $contactType, $client_number);
                $stmt->execute();

                $log_data = [
                    'entity_type' => 'contact',
                    'entity_id' => $conn->insert_id,
                    'event_type' => 'create',
                    'event_description' => 'Phone contact created for new client: ' . $client_number,
                    'user_id' => $user_id
                ];
                
                logEvent($log_data);

                // email
                $contactType = 'email';
                $stmt = $conn->prepare($insertContactQuery);
                $stmt->bind_param("iss", $client_id, $contactType, $client_email);
                $stmt->execute();

                $log_data = [
                    'entity_type' => 'contact',
                    'entity_id' => $conn->insert_id,
                    'event_type' => 'create',
                    'event_description' => 'Email contact created for new client: ' . $client_email,
                    'user_id' => $user_id
                ];
                
                logEvent($log_data);
            }

            // address information
            $city = $_POST['address']['city'];
            $barangay = $_POST['address']['barangay'];
            $street = $_POST['address']['street'];
            $number = $_POST['address']['number'];

            $checkAddressQuery = "SELECT address_id FROM addresses WHERE client_id = ? AND city = ? AND barangay = ? AND street = ? AND house_number = ?";
            $stmt = $conn -> prepare($checkAddressQuery);
            $stmt -> bind_param("issss", $client_id, $city, $barangay, $street, $number);
            $stmt -> execute();
            $stmt -> store_result();

            if ($stmt -> num_rows > 0) {
                $stmt -> bind_result($address_id);
                $stmt -> fetch();

                $log_data = [
                    'entity_type' => 'address',
                    'entity_id' => $address_id,
                    'event_type' => 'reuse',
                    'event_description' => 'Address reused for client.',
                    'user_id' => $user_id
                ];

                logEvent($log_data);

            } else {
                $insertAddressQuery = 'INSERT INTO addresses (client_id, city, barangay, street, house_number) VALUES (?, ?, ?, ?, ?)';
                $stmt = $conn -> prepare($insertAddressQuery);
                $stmt -> bind_param("issss", $client_id, $city, $barangay, $street, $number);
                $stmt -> execute();
                $address_id = $conn -> insert_id;

                $log_data = [
                    'entity_type' => 'address',
                    'entity_id' => $address_id,
                    'event_type' => 'create',
                    'event_description' => 'New address created for client.',
                    'user_id' => $user_id
                ];

                logEvent($log_data);
            }

            // order information
            $total_amount = $_POST['total_amount'];
            $total_qty = $_POST['total_qty'];
            $insertOrderQuery = "INSERT INTO orders (client_id, address_id, total_qty, total_amount) VALUES (?, ?, ?, ?)";
            $stmt = $conn -> prepare($insertOrderQuery);
            $stmt -> bind_param("iiid", $client_id, $address_id, $total_qty, $total_amount);
            $stmt -> execute();
            $order_id = $conn -> insert_id;

            // order item information
            $log_data = [
                'entity_type' => 'order',
                'entity_id' => $order_id,
                'event_type' => 'create',
                'event_description' => 'Order created for client.',
                'user_id' => $user_id
            ];

            logEvent($log_data);

            $order_items = $_POST['items'];

            $insertItemQuery = "INSERT INTO order_items (order_id, item_id, price, item_total, truck_type_id) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn -> prepare($insertItemQuery);

            foreach($order_items as $item) {
                $item_id = $item['item_id'];
                $quantity = $item['quantity']; 
                $price = $item['price'];
                $unit_capacity = $item['unit_capacity'];
                $total = $price * $unit_capacity;
                $unit_type = $item['unit_type_id'];

                // Loop through each quantity and insert as individual items
                for ($i = 0; $i < $quantity; $i++) {
                    $stmt -> bind_param("iiddi", $order_id, $item_id, $price, $total, $unit_type);
                    $stmt -> execute();

                    $log_data = [
                        'entity_type' => 'order_item',
                        'entity_id' => $conn->insert_id,
                        'event_type' => 'create',
                        'event_description' => 'Order item inserted for order ID: ' . $order_id,
                        'user_id' => $user_id
                    ];
                    
                    logEvent($log_data);
                }
            }

            $stmt -> close();

            mysqli_commit($conn);
            echo json_encode(['status' => 'success', 'message' => 'Order saved successfully.']);

        } catch(Exception $e) {
            mysqli_rollback($conn);
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    function getClientInfo() {
        global $conn;
        $client_id = $_POST['client_id'];

        $clientQuery = 
        "SELECT c.client_id, c.name,
            (SELECT contact_value FROM contacts WHERE client_id = c.client_id AND contact_type = 'phone' ORDER BY id DESC LIMIT 1) AS latest_phone,
            (SELECT contact_value FROM contacts WHERE client_id = c.client_id AND contact_type = 'email' ORDER BY id DESC LIMIT 1) AS latest_email,
            (SELECT a.city FROM addresses a WHERE a.client_id = c.client_id ORDER BY address_id DESC LIMIT 1) AS latest_city,
            (SELECT a.barangay FROM addresses a WHERE a.client_id = c.client_id ORDER BY address_id DESC LIMIT 1) AS latest_barangay,
            (SELECT a.street FROM addresses a WHERE a.client_id = c.client_id ORDER BY address_id DESC LIMIT 1) AS latest_street,
            (SELECT a.house_number FROM addresses a WHERE a.client_id = c.client_id ORDER BY address_id DESC LIMIT 1) AS latest_house_number
        FROM clients c
        WHERE c.client_id = ?";

        $stmt = $conn -> prepare($clientQuery);
        $stmt -> bind_param("i", $client_id);
        $stmt -> execute();
        $result = $stmt -> get_result();

        if ($result -> num_rows > 0) {
            $client_info = $result -> fetch_assoc();
            echo json_encode([
                'success' => true,
                'client_name' => $client_info['name'],
                'address' => [
                    'city' => $client_info['latest_city'],
                    'barangay' => $client_info['latest_barangay'],
                    'street' => $client_info['latest_street'],
                    'house_number' => $client_info['latest_house_number']
                ],
                'phone' => $client_info['latest_phone'],
                'email' => $client_info['latest_email']
            ]);
        } else {
            echo json_encode(['success' => false]);
        }
    }

    function searchClients() {
        global $conn;

        $query = $_POST['query'];
        $searchQuery = "SELECT * FROM clients WHERE name LIKE ? LIMIT 3";
        $stmt = $conn -> prepare($searchQuery);
        $searchTerm = '%' . $query . '%';
        $stmt -> bind_param("s", $searchTerm);
        $stmt -> execute();
        $result = $stmt -> get_result();

        $clients = [];
        while ($row = $result -> fetch_assoc()) {
            $clients[] = $row;
        }

        echo json_encode($clients);
    }

    /**
     * Retrieves data from a database table.
     *
     * @param string $tableName The name of the table to query.
     * @param string|array $columns The columns to select. If an array, the values will be sanitized and joined with commas.
     * @param string $joins The joins to include in the query. If empty, no joins will be added.
     * @param string $where The WHERE clause of the query. If empty, no WHERE clause will be added.
     * @param string $orderBy The ORDER BY clause of the query. If empty, no ORDER BY clause will be added.
     *
     * @return array The results of the query, as an array of associative arrays, where each associative array
     *     represents a row of the result set.
     */
    
    function dbGetTableData($tableName, $columns = '*', $joins = '', $where = '', $orderBy = '', $limit = null , $offset = null, $search = null) {
        global $conn;
    
        $tableName = mysqli_real_escape_string($conn, $tableName);
        $columns = is_array($columns) ? implode(', ', array_map(fn($col) => mysqli_real_escape_string($conn, $col), $columns)) : $columns;

        if ($search) {
            $searchColumn = mysqli_real_escape_string($conn, $search['column']);
            $searchQuery = mysqli_real_escape_string($conn, $search['query']);
            $searchWhere = "$searchColumn LIKE '%$searchQuery%'";

            $where = !empty($where) ? "$where AND $searchWhere" : $searchWhere;
        }

        $sql = "SELECT $columns FROM $tableName" .
            (!empty($joins) ? " $joins" : "") .
            (!empty($where) ? " WHERE $where" : "") .
            (!empty($orderBy) ? " ORDER BY $orderBy" : "") .
            (!empty($limit) ? " LIMIT $limit" : "") .
            (!empty($offset) ? " OFFSET $offset" : "");

        $result = $conn->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    function dbGetTableCount($table, $where = '') {
        global $conn;
        $query = "SELECT COUNT(*) as count FROM $table";
        if ($where) {
            $query .= " WHERE $where";
        }
        $result = $conn->query($query);
        $row = $result->fetch_assoc();
        return $row['count'];
    }

    function dbAddRecord($table, $data) {
        global $conn;

        $columns = implode(", ", array_keys($data));
        $placeholders = implode(", ", array_fill(0, count($data), "?"));
        $values = array_values($data);

        $types = "";
        foreach ($values as $value) {
            if (is_int($value)) {
                $types .= "i";
            } elseif (is_float($value)) {
                $types.= "d";
            } else {
                $types .= "s";
            };
        }

        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        $stmt = $conn -> prepare($sql);

        if ($stmt === false) {
            die ("Prepare failed: " . $conn -> error);
        }

        $stmt -> bind_param($types, ...$values);

        $result = false;
        
        if ($stmt -> execute()) {
            
            $result = true;
        }
        
        $stmt -> close();
        return $result;
    }

    function addUnit() {
        global $conn;

        parse_str($_POST['formData'], $unitData);
        
        $unitData = [
            'truck_number' => $unitData['truck_number'],
            'truck_type_id' => $unitData['truck_type_id']
        ];

        $result = dbAddRecord('trucks', $unitData);

        if($result === true) {
            $user_id = getCurrentOfficer('id'); 
            $last_inserted_id = mysqli_insert_id($conn);

            $log_data = [
                'entity_type' => 'truck', 
                'entity_id' => $last_inserted_id, 
                'event_type' => 'create',
                'event_description' => 'Truck added: ' . $unitData['truck_number'], 
                'user_id' => $user_id 
            ];

            logEvent($log_data);

            echo "success";
        } else {
            echo "error";
        };
    }

    function addUnitType() {
        global $conn;

        parse_str($_POST['formData'], $unitTypeData);

        $unitTypeData = [
            'type_name' => $unitTypeData['type_name'],
            'capacity' => $unitTypeData['capacity']
        ];
    
        $result = dbAddRecord('truck_types', $unitTypeData);
    
        if ($result === true) {
            echo "success";
            
            $last_inserted_id = mysqli_insert_id($conn);

            $logData = [
                'entity_type' => 'truck type',
                'entity_id' => $last_inserted_id,
                'event_type' => 'create',
                'event_description' => 'New unit type added: ' . $unitTypeData['type_name'],
                'user_id' => getCurrentOfficer('id')
            ];
    
            logEvent($logData); 

        } else {
            echo "error";
        }
    }

    function addDriver() { 
        global $conn;
        $user_id = getCurrentOfficer('id');

        // Parse the serialized form data
        parse_str($_POST['formData'], $driverData);
    
        // Prepare data for insertion
        $driverData = [
            'name' => $driverData['name'],
            'license_number' => $driverData['license_number'],
            'phone_number' => $driverData['phone_number'],
            'status' => $driverData['status'],
        ];
    
        // Add the record to the 'drivers' table
        $result = dbAddRecord('drivers', $driverData);
    
        // Return success or error response
        if ($result === true) {
            echo "success";

            $log_data = [
                'entity_type' => 'driver',
                'entity_id' => $conn->insert_id,
                'event_type' => 'create',
                'event_description' => 'New driver added: ' . $driverData['name'],
                'user_id' => $user_id
            ];

            logEvent($log_data);

        } else {
            echo "error";
        }
    }
    
    function getOrderData($orderId) {
        global $conn;
    
        // Fetch order details including client_id
        $columns = [
            'o.id', 
            'o.client_id', 
            'o.created_at', 
            'c.name AS client_name', 
            'a.city, a.barangay, a.street, a.house_number'
        ];
        $joins = "
            JOIN clients c ON o.client_id = c.client_id
            JOIN addresses a ON o.address_id = a.address_id
        ";
        $where = "o.id = " . intval($orderId);
        $orderDetails = dbGetTableData('orders o', $columns, $joins, $where);
    
        if (empty($orderDetails)) {
            return null; 
        }
    
        // Get the client_id for fetching contacts
        $clientId = $orderDetails[0]['client_id'];
    
        // Fetch phone number
        $phoneQuery = "
            SELECT contact_value 
            FROM contacts 
            WHERE client_id = ? AND contact_type = 'phone' LIMIT 1
        ";
        $stmt = $conn->prepare($phoneQuery);
        $stmt->bind_param('i', $clientId);
        $stmt->execute();
        $stmt->bind_result($phone);
        $stmt->fetch();
        $stmt->close();
        
        // Fetch email
        $emailQuery = "
            SELECT contact_value 
            FROM contacts 
            WHERE client_id = ? AND contact_type = 'email' LIMIT 1
        ";
        $stmt = $conn->prepare($emailQuery);
        $stmt->bind_param('i', $clientId);
        $stmt->execute();
        $stmt->bind_result($email);
        $stmt->fetch();
        $stmt->close();
    
        $orderDetails[0]['phone'] = $phone;
        $orderDetails[0]['email'] = $email;
    
        $orderDetails[0]['full_address'] = 
            $orderDetails[0]['house_number'] . ', ' . 
            $orderDetails[0]['street'] . ' Street, ' . 
            $orderDetails[0]['barangay'] . ', ' . 
            $orderDetails[0]['city'];
    
        unset($orderDetails[0]['house_number'], $orderDetails[0]['street'], $orderDetails[0]['barangay'], $orderDetails[0]['city']);
    
        // Fetch order items with item name
        $columns = [ 
            'oi.price', 
            'oi.item_total', 
            'oi.status',
            'oi.id',
            'oi.truck_type_id', 
            'tt.type_name', 
            'i.item_name'
        ];
        $joins = "
            JOIN truck_types tt ON oi.truck_type_id = tt.id
            JOIN items i ON oi.item_id = i.item_id"; 
        $where = "oi.order_id = " . intval($orderId); 
        $orderItems = dbGetTableData('order_items oi', $columns, $joins, $where);
    
        // Return the combined result
        return [
            'order' => $orderDetails[0], 
            'items' => $orderItems
        ];
    }

    function getDrivers() {

        global $conn;

        $query = "
            SELECT id, name, license_number, phone_number, status 
            FROM drivers 
            ORDER BY 
            CASE status
                WHEN 'available' THEN 1
                WHEN 'on_trip' THEN 2
                WHEN 'unavailable' THEN 3
            END ASC";
        
        $result = $conn->query($query);
        
        if ($result->num_rows > 0) {
            $drivers = [];
            while ($row = $result->fetch_assoc()) {
                $drivers[] = $row;
            }
            return $drivers; 
        }

        return []; 
    }

    function getOperators($limit = 0, $offset = 0) {
        $result = dbGetTableData('drivers', limit: $limit, offset: $offset);

        return $result;
    }

    function returnItems($limit = 0, $offset = 0) {
        $result = dbGetTableData('items', limit: $limit, offset: $offset);

        return $result;
    }

    function getClients($limit = 0, $offset = 0) {
        $result = dbGetTableData('clients', limit: $limit, offset: $offset);

        return $result;
    }

    function getUnits($limit = 10, $offset = 0) {

        $limit = (int)$limit;
        $offset = (int)$offset;

        global $conn;
    
        $query = "
            SELECT t.id, t.truck_number, t.truck_type_id, t.status, t.created_at, t.updated_at, tt.type_name AS truck_type
            FROM trucks t
            LEFT JOIN truck_types tt ON t.truck_type_id = tt.id
            LIMIT $limit OFFSET $offset";
    
        $result = $conn->query($query);
    
        if ($result->num_rows > 0) {
            $trucks = [];
            while ($row = $result->fetch_assoc()) {
                $trucks[] = $row; 
            }
            return $trucks; 
        }
    
        return [];
    }
    
    function getAddresses($limit = 10, $offset = 0) {

        $result = dbGetTableData('addresses', '*', '', '', '', $limit, $offset);

        return $result;
    }

    function getTrucks($limit = 10, $offset = 0) {

        $result = dbGetTableData('trucks', '*', '', '', '', $limit, $offset);

        return $result;
    }

    function getUnitTypes($limit = 0, $offset = 0) {
        $result = dbGetTableData('truck_types', offset: $offset, limit: $limit);

        return $result;
    }

    function getOrders($limit = 0, $offset = 0) {
        $result = dbGetTableData('orders', limit: $limit, offset: $offset);

        // add proper address display
        // $address = 
        // array_push($result, 'address');

        return $result;
    }

    function getOrderItems ($limit = 0, $offset = 0) {
        $result = dbGetTableData('order_items', limit: $limit, offset: $offset);

        return $result;
    }

    function getUnitsFiltered($unit_type_id) {
        
        $trucks = getUnits();
    
        $filteredTrucks = array_filter($trucks, function($truck) use ($unit_type_id) {
            return $truck['truck_type_id'] == $unit_type_id; 
        });
    
        usort($filteredTrucks, function($a, $b) {
            $statusOrder = ['available' => 1, 'in_use' => 2, 'maintenance' => 3, 'out_of_service' => 4];
            return $statusOrder[$a['status']] - $statusOrder[$b['status']];
        });
    
        return $filteredTrucks; 
    }

    function addDispatchRecord($order_item_id, $unit_id, $operator_id, $officer_id) {

        $success = false;
        
        $insert_query = "INSERT INTO dispatch (order_item_id, truck_id, driver_id, dispatch_officer_id, status, created_at, updated_at) 
                        VALUES (?, ?, ?, ?, 'in-queue', NOW(), NOW())";
        
        if (dbExecuteQuery($insert_query, $order_item_id, $unit_id, $operator_id, $officer_id)) {
    
            $update_query = "UPDATE order_items SET status = ? WHERE id = ?";
            
            if (dbExecuteQuery($update_query, 'in-queue', $order_item_id)) {

                // logging event
                $logData = [
                    'entity_type' => 'dispatch',
                    'entity_id' => $order_item_id,
                    'event_type' => 'create',
                    'event_description' => "Dispatch record created for Order $order_item_id.",
                    'user_id' => $officer_id
                ];

                logEvent($logData);

                $success = true;
            }
        }
    
        return $success;
    }
    
    function getDispatchPendingOrdersHtml() {
    
        global $conn;
            $columns = ['o.id', 'o.client_id', 'o.created_at', 'c.name', 'o.status'];
            $joins = "JOIN clients c ON o.client_id = c.client_id";
            $where = "o.status = 'pending'";
            $orderBy = "o.created_at ASC";

            $pendingOrders = dbGetTableData('orders o', $columns, $joins, $where, $orderBy);

            $output = '<ul class="list-group" id="order-list-pending">';
            foreach ($pendingOrders as $order) {
                // Fetch counts for each status of order items
                $orderId = $order['id'];
                $query = "
                    SELECT 
                        SUM(status = 'pending') AS pending_count,
                        SUM(status = 'in-queue') AS in_queue_count, 
                        SUM(status = 'in-progress') AS in_progress_count, 
                        SUM(status = 'completed') AS completed_count,
                        SUM(status = 'failed') AS failed_count,
                        SUM(status = 'canceled') AS canceled_count
                    FROM order_items
                    WHERE order_id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param('i', $orderId);
                $stmt->execute();
                $stmt->bind_result($pendingCount, $inQueueCount, $inProgressCount, $completedCount, $failedCount, $canceledCount);
                $stmt->fetch();
                $stmt->close();

                // Build the list item
                $output .= '<li class="order" data-order-id="' . $order['id'] . '">
                    <div class="d-flex">
                        <div class="section">
                            <small class="text-body-secondary">' . sprintf('%04d', $order['id']) . '</small>
                            <div class="mx-2 text-nowrap overflow-x-auto">
                                <span class="fw-bold">' . $order['name'] . '</span>
                            </div>
                        </div>
                        <div class="section">
                            <small id="order-list-pending-date">' . date("m/d/y", strtotime($order['created_at'])) . '</small>
                            <div>
                                <span class="badge text-bg-secondary">' . $pendingCount . '</span> 
                                <span class="badge text-bg-primary">' . $inQueueCount . '</span> 
                                <span class="badge text-bg-info">' . $inProgressCount . '</span> <br>
                                <span class="badge text-bg-success">' . $completedCount . '</span> 
                                <span class="badge text-bg-dark">' . $failedCount . '</span> 
                                <span class="badge text-bg-danger">' . $canceledCount . '</span> 
                            </div>
                        </div>
                    </div>
                </li>';
            }

        $output .= '</ul>';

        return $output; // Return the generated HTML
    }

    function dbExecuteQuery($query, ...$params) {
        global $conn;
    
        $stmt = $conn->prepare($query);
    
        if ($stmt === false) {
            die("Prepare failed: " . $conn->error);
        }
    
        if (!empty($params)) {
            $types = '';
            foreach ($params as $param) {
                if (is_int($param)) {
                    $types .= 'i'; // Integer
                } elseif (is_double($param)) {
                    $types .= 'd'; // Double
                } elseif (is_string($param)) {
                    $types .= 's'; // String
                } else {
                    $types .= 'b'; // Blob and other types
                }
            }
    
            $stmt->bind_param($types, ...$params);
        }
    
        $result = $stmt->execute();
    
        $stmt->close();
    
        return $result;
    }
    
    function getDispatchRecords() {
        global $conn;
    
        $query = "SELECT 
            d.*, 
            t.truck_number, 
            tt.capacity AS truck_capacity, 
            dr.id AS driver_id, 
            dr.name AS driver_name, 
            do.id AS officer_id, 
            do.name AS officer_name, 
            oi.item_id, 
            oi.item_total,
            i.item_name,
            i.price AS item_price,
            o.id AS order_id,
            c.name AS client_name,
            a.city, 
            a.barangay, 
            a.street, 
            a.house_number,
            contact_info.phone AS client_phone, 
            contact_info.email AS client_email
        FROM dispatch d
        JOIN trucks t ON d.truck_id = t.id
        JOIN truck_types tt ON t.truck_type_id = tt.id  
        JOIN drivers dr ON d.driver_id = dr.id
        JOIN dispatch_officers do ON d.dispatch_officer_id = do.id
        JOIN order_items oi ON d.order_item_id = oi.id
        JOIN items i ON oi.item_id = i.item_id
        JOIN orders o ON oi.order_id = o.id 
        JOIN clients c ON o.client_id = c.client_id  
        JOIN addresses a ON o.address_id = a.address_id 
        LEFT JOIN (
            SELECT client_id,
                MAX(CASE WHEN contact_type = 'phone' THEN contact_value END) AS phone,
                MAX(CASE WHEN contact_type = 'email' THEN contact_value END) AS email
            FROM contacts
            GROUP BY client_id
        ) AS contact_info ON c.client_id = contact_info.client_id
        ORDER BY d.created_at DESC;";
        
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
    
        $dispatches = [
            'in-queue' => [],
            'in-transit' => [],
            'successful' => [],
            'failed' => []
        ];
    
        // Group the results by their status
        while ($row = $result->fetch_assoc()) {
            $status = $row['status']; 
            if (array_key_exists($status, $dispatches)) {
                $dispatches[$status][] = $row; 
            }
        }
    
        $stmt->close();
    
        return $dispatches;
    }

    function updateDispatchStatus($dispatch_id, $new_status, $failed_reason, $failed_type) {
        $update_query = "UPDATE dispatch SET status = ?, updated_at = NOW() WHERE id = ?";
        
        if (dbExecuteQuery($update_query, $new_status, $dispatch_id)) {
            
            $dispatch_data = dbGetTableData('dispatch', ['order_item_id', 'truck_id', 'driver_id'], '', "id = $dispatch_id");
            $order_item_id = $dispatch_data[0]['order_item_id'];
            $unit_id = $dispatch_data[0]['truck_id'];
            $driver_id = $dispatch_data[0]['driver_id'];

            $log_description = "Status updated to $new_status.";
            
            switch ($new_status) {
                case 'in-queue':
                    $order_item_status = 'in-queue';
                    // $driver_status = 'available';
                    // $unit_status = 'available';

                    updateOrderItemStatus($order_item_id, $order_item_status);
                    // updateUnitStatus($unit_id, $unit_status);
                    // updateDriverStatus($driver_id, $driver_status);
                    break;

                case 'in-transit':
                    $unit_status = dbGetStatus('trucks', 'id', $unit_id);
                    $driver_status = dbGetStatus('drivers', 'id', $driver_id);
                    
                    if ($unit_status !== 'available' || $driver_status !== 'available') {
                        return ['success' => false, 'message' => 'Truck or Driver is not available for transit.'];
                    }

                    $order_item_status = 'in-progress';
                    $driver_status = 'on_trip';
                    $unit_status = 'in_use';

                    updateOrderItemStatus($order_item_id, $order_item_status);
                    updateUnitStatus($unit_id, $unit_status);
                    updateDriverStatus($driver_id, $driver_status);
                    break;

                case 'successful':
                    $order_item_status = 'completed';
                    $driver_status = 'available';
                    $unit_status = 'available';

                    updateOrderItemStatus($order_item_id, $order_item_status);
                    updateUnitStatus($unit_id, $unit_status);
                    updateDriverStatus($driver_id, $driver_status);
                    break;

                case 'failed':
                    $order_item_status = 'failed';
                    $driver_status = 'available';
                    $unit_status = 'available';
                    
                    updateOrderItemStatus($order_item_id, $order_item_status);
                    updateUnitStatus($unit_id, $unit_status);
                    updateDriverStatus($driver_id, $driver_status);
                    
                    if ($failed_reason && $failed_type) {
                        $log_description = "Status updated to $new_status. $failed_type $failed_reason";
                    }
                    break;

                case 'remove':
                    removeDispatchRecord($dispatch_id);
                    break;

                default:
                    break;
            }

            $log_data = [
                'entity_type' => 'dispatch',
                'entity_id' => $dispatch_id,
                'event_type' => 'update',
                'event_description' => $log_description,
                'user_id' => getCurrentOfficer('id')
            ];

            logEvent($log_data);
            
            return ['success' => true, 'message' => 'Dispatch status updated successfully.'];
        }
    
        return ['success' => false, 'message' => 'Failed to update dispatch status.'];
    }

    function updateOrderItemStatus($order_item_id, $new_status) {
        $update_order_query = "UPDATE order_items SET status = ? WHERE id = ?";
        dbExecuteQuery($update_order_query, $new_status, $order_item_id);
    };

    function updateUnitStatus($unit_id, $unit_status) {
        $update_truck_query = "UPDATE trucks SET status = ? WHERE id = ?";
        dbExecuteQuery($update_truck_query, $unit_status, $unit_id);
    };

    function updateDriverStatus($driver_id, $driver_status) {
        $update_driver_query = "UPDATE drivers SET status = ? WHERE id = ?";
        dbExecuteQuery($update_driver_query, $driver_status, $driver_id);
    };


    function removeDispatchRecord($dispatch_id) {
        $dispatch_data = dbGetTableData('dispatch', ['order_item_id'], '', "id = $dispatch_id");
    
        $order_item_id = $dispatch_data[0]['order_item_id'];
        $reset_order_query = "UPDATE order_items SET status = ? WHERE id = ?";
        dbExecuteQuery($reset_order_query, 'pending', $order_item_id);  

        $delete_dispatch_query = "DELETE FROM dispatch WHERE id = ?";
        dbExecuteQuery($delete_dispatch_query, $dispatch_id);
    };
    
    function dbGetStatus($table, $id_column, $id_value) {
        global $conn;
    
        $query = "SELECT status FROM $table WHERE $id_column = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id_value);
        $stmt->execute();
        $stmt->bind_result($status);
        $stmt->fetch();
        $stmt->close();
    
        return $status;
    }

    function modalTitle($title) {
        echo str_pad($title, 30, ' .');
    };
    
    function logEvent($log_data) {
        global $conn;
        $stmt = $conn->prepare("INSERT INTO logs (entity_type, entity_id, event_type, event_description, user_id) VALUES (?, ?, ?, ?, ?)");

        $stmt->bind_param(
            "sisss", 
            $log_data['entity_type'], 
            $log_data['entity_id'], 
            $log_data['event_type'], 
            $log_data['event_description'], 
            $log_data['user_id']);
        if($stmt->execute()){
            $stmt->close();
            return true;
        };

        return false;
    }

    function getLogs() {
        global $conn;
        $query = "SELECT * FROM logs ORDER BY id DESC";
        $result = $conn->query($query);
        
        if ($result->num_rows > 0) {
            return $result->fetch_all(MYSQLI_ASSOC);
        } else {
            return []; 
        }
    }

    function getDispatchOfficers ($limit = 0, $offset = 0) {
        global $conn;
        
        $result = dbGetTableData('dispatch_officers', limit: $limit, offset: $offset);

        return $result;
    }
    
    function dbDeleteRow($table, $id_column, $value) {
        global $conn;
    
        $query = "DELETE FROM `$table` WHERE `$id_column` = ?";
        $stmt = $conn->prepare($query);
        if ($stmt === false) {
            return ['success' => true, 'message' => 'Failed to prepare statement'];
        }

        $stmt->bind_param('s', $value); 
        
        // Execute the statement
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Row deleted successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to delete row: ' . $stmt->error];
        }
    }

    function dbCheckDependencies($id, $dependency_checks) {
        global $conn;
        $dependencies_found = [];

        foreach ($dependency_checks as $dependency) {
            $dependency_table = $dependency['table'];
            $dependency_column = $dependency['column'];

            $query = "SELECT COUNT(*) as count FROM `$dependency_table` WHERE `$dependency_column` = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('s', $id);
            $stmt->execute();

            $result = $stmt->get_result();
            $count = $result->fetch_assoc()['count'];

            if ($count > 0) {
                $dependencies_found[] = [
                    'table' => $dependency_table,
                    'column' => $dependency_column,
                    'count' => $count
                ];
            }

            $stmt->close();
        }

        if (count($dependencies_found) > 0) {
            return [
                'success' => true,
                'dependencies' => $dependencies_found
            ];
        } else {
            return [
                'success' => false,
                'dependencies' => 'none'  // No dependencies found
            ];
        }

    }

    function reassignDependencies($table, $column, $id, $reassign_value) {
        global $conn;

        if ($reassign_value === '') {
            $query = "UPDATE `$table` SET `$column` = NULL WHERE `$column` = ?";
        } else {
            $query = "UPDATE `$table` SET `$column` = ? WHERE `$column` = ?";
        }
    
        $stmt = $conn->prepare($query);
    
        if ($stmt === false) {
            return [
                'success' => false,
                'message' => 'Failed to prepare statement: ' . $conn->error
            ];
        }
    
        if ($reassign_value === '') {
            $stmt->bind_param('i', $id);
        } else {
            $stmt->bind_param('si', $reassign_value, $id);
        }
    
        if ($stmt->execute()) {
            $affected_rows = $stmt->affected_rows;
            $stmt->close();
    
            return [
                'success' => true,
                'message' => "Reassignment successful. $affected_rows rows updated."
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to execute query: ' . $stmt->error
            ];
        }
    }

    function getLatestLoginEvent($user_id) {
        
        $tableName = 'logs';
        $columns = '*'; 
        $joins = ''; 
        $where = "event_type = 'login' AND entity_id = $user_id";
        $orderBy = 'timestamp DESC'; 
        $limit = 1; 
    
        
        $result = dbGetTableData($tableName, $columns, $joins, $where, $orderBy, $limit);
    
        
        return $result ? $result[0] : null; 
    }
    