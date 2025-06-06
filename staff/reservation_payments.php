<?php
session_start();
require_once('../config/config.php');
require_once('../config/codeGen.php');
require_once('../config/checklogin.php');
staff(); /* Invoke  Check Login */


require_once("../partials/head.php");
?>

<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
    <div class="wrapper">
        <!-- Navbar -->
        <?php require_once("../partials/admin_nav.php"); ?>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <?php require_once("../partials/staff_sidebar.php"); ?>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <section class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1>Quản lý thanh toán đặt phòng</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="#">Trang chủ</a></li>
                                <li class="breadcrumb-item"><a href="dashboard.php">Bảng điều khiển</a></li>
                                <li class="breadcrumb-item"><a href="reservations.php">Đặt phòng</a></li>
                                <li class="breadcrumb-item active">Thanh toán</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </section>

            <section class="content">
                <div class="container-fluid">
                    <form class="form-inline">
                    </form>
                    <div class="text-right">
                        <a href="add_reservation_payment.php" class="btn btn-primary">Thêm thanh toán đặt phòng</a>
                    </div>
                    <hr>
                    <div class="col-12">
                        <table id="dt-1" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Mã thanh toán</th>
                                    <th>Số tiền</th>
                                    <th>Tên khách hàng</th>
                                    <th>Phương thức thanh toán</th>
                                    <th>Ngày thanh toán</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php
                                // Sửa lại truy vấn để lấy đúng các bản ghi thanh toán đặt phòng (cả tiếng Việt và tiếng Anh)
                                $ret = "SELECT * FROM `payments` WHERE service_paid = 'Đặt phòng' OR service_paid = 'Reservations'";
                                $stmt = $mysqli->prepare($ret);
                                $stmt->execute();
                                $res = $stmt->get_result();
                                while ($payments = $res->fetch_object()) {
                                ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($payments->code); ?></td>
                                        <td><?php echo number_format($payments->amt, 0, ',', ','); ?> VND</td>
                                        <td><?php echo htmlspecialchars($payments->cust_name); ?></td>
                                        <td><?php echo htmlspecialchars($payments->payment_means); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($payments->created_at)); ?></td>
                                        <td>
                                            <a class="badge badge-success" data-toggle="modal" href="#receipt-<?php echo $payments->id; ?>">In hóa đơn</a>
                                            <!-- Print Receipt -->
                                            <div class="modal fade" id="receipt-<?php echo $payments->id; ?>">
                                                <div class="modal-dialog modal-xl">
                                                    <div class="modal-content">
                                                        <div class="modal-body">
                                                            <div id="Print_Receipt" class="invoice p-3 mb-3">
                                                                <div class="row">
                                                                    <div class="col-12 ">
                                                                        <h4 class="text-center">
                                                                            <?php
                                                                            // Lấy logo động từ system_settings
                                                                            $ret_logo = "SELECT sys_logo FROM system_settings LIMIT 1";
                                                                            $stmt_logo = $mysqli->prepare($ret_logo);
                                                                            $stmt_logo->execute();
                                                                            $stmt_logo->bind_result($sys_logo);
                                                                            $stmt_logo->fetch();
                                                                            $stmt_logo->close();
                                                                            if (empty($sys_logo)) {
                                                                                $logo_dir = '../public/uploads/sys_logo/logo.png';
                                                                            } else {
                                                                                $logo_dir = "../public/uploads/sys_logo/$sys_logo";
                                                                            }
                                                                            ?>
                                                                            <img height="100" width="200" src="<?php echo $logo_dir; ?>" class="img-thumbnail img-fluid" alt="System Logo">
                                                                            <br>
                                                                            <small class="float-right">Ngày: <?php echo date('d/m/Y');?></small>
                                                                        </h4>
                                                                        <h4>
                                                                        NT Hotels Inc
                                                                        </h4>
                                                                    </div>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-12 table-responsive">
                                                                        <table class="table">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th>Tên khách hàng</th>
                                                                                    <th>Số tiền đã thanh toán</th>
                                                                                    <th>Dịch vụ đã thanh toán</th>
                                                                                    <th>Phương thức thanh toán</th>
                                                                                    <th>Mã thanh toán</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                <tr>
                                                                                    <td><?php echo htmlspecialchars($payments->cust_name);?></td>
                                                                                    <td><?php echo number_format($payments->amt, 0, ',', ','); ?> VND</td>
                                                                                    <td><?php echo htmlspecialchars($payments->service_paid);?></td>
                                                                                    <td><?php echo htmlspecialchars($payments->payment_means);?></td>
                                                                                    <td><?php echo htmlspecialchars($payments->code);?></td>
                                                                                </tr>
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer justify-content-between">
                                                            <button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>
                                                            <button id="print" onclick="printContent('Print_Receipt');"  type="button" class="btn btn-primary" >In</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Print Receipt -->
                                        </td>
                                    </tr>
                                <?php
                                } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
        <?php require_once("../partials/footer.php"); ?>

    </div>
    <?php require_once("../partials/scripts.php"); ?>

</body>

</html>