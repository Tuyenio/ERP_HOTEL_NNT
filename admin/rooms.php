<?php
session_start();
require_once('../config/config.php');
require_once('../config/codeGen.php');
require_once('../config/checklogin.php');
sudo(); /* Invoke Admin Check Login */

if (isset($_POST['Add_Room'])) {
    /* Error Handling And Add Room */
    $error = 0;
    if (isset($_POST['id']) && !empty($_POST['id'])) {
        $id = mysqli_real_escape_string($mysqli, trim($_POST['id']));
    } else {
        $error = 1;
        $err = "Room ID  Cannot Be Empty";
    }

    if (isset($_POST['number']) && !empty($_POST['number'])) {
        $number = mysqli_real_escape_string($mysqli, trim($_POST['number']));
    } else {
        $error = 1;
        $err = "Room Number  Cannot Be Empty";
    }

    if (isset($_POST['type']) && !empty($_POST['type'])) {
        $type = mysqli_real_escape_string($mysqli, trim($_POST['type']));
    } else {
        $error = 1;
        $err = "Room Type Cannot Be Empty";
    }

    if (isset($_POST['price']) && !empty($_POST['price'])) {
        $price = mysqli_real_escape_string($mysqli, trim($_POST['price']));
    } else {
        $error = 1;
        $err = "Room Price Cannot Be Empty";
    }

    if (isset($_POST['status']) && !empty($_POST['status'])) {
        $status = mysqli_real_escape_string($mysqli, trim($_POST['status']));
    } else {
        $error = 1;
        $err = "Room Status Cannot Be Empty";
    }

    if (isset($_POST['details']) && !empty($_POST['details'])) {
        $details = mysqli_real_escape_string($mysqli, trim($_POST['details']));
    } else {
        $error = 1;
        $err = "Room details Cannot Be Empty";
    }

    if (!$error) {
        //Prevent Double Entries
        $sql = "SELECT * FROM  rooms WHERE number = '$number'  ";
        $res = mysqli_query($mysqli, $sql);
        if (mysqli_num_rows($res) > 0) {
            $row = mysqli_fetch_assoc($res);
            if ($number == $row['number']) {
                $err =  "A Room With That Number Already Exists";
            } else {
                //
            }
        } else {
            $image = $_FILES['image']['name'];
            move_uploaded_file($_FILES["image"]["tmp_name"], "../public/uploads/rooms/" . $_FILES["image"]["name"]);
            $query = "INSERT INTO rooms (id, number, type, price, status, details, image) VALUES (?,?,?,?,?,?,?)";
            $stmt = $mysqli->prepare($query);
            $rc = $stmt->bind_param('sssssss', $id, $number, $type, $price, $status, $details, $image);
            $stmt->execute();
            if ($stmt) {
                $success = "Added" && header("refresh:1; url=rooms.php");
            } else {
                $info = "Please Try Again Or Try Later";
            }
        }
    }
}

if (isset($_POST['Update_Room'])) {
    /* Error Handling And Update Room */
    $error = 0;
    if (isset($_POST['id']) && !empty($_POST['id'])) {
        $id = mysqli_real_escape_string($mysqli, trim($_POST['id']));
    } else {
        $error = 1;
        $err = "Room ID  Cannot Be Empty";
    }

    if (isset($_POST['number']) && !empty($_POST['number'])) {
        $number = mysqli_real_escape_string($mysqli, trim($_POST['number']));
    } else {
        $error = 1;
        $err = "Room Number  Cannot Be Empty";
    }

    if (isset($_POST['type']) && !empty($_POST['type'])) {
        $type = mysqli_real_escape_string($mysqli, trim($_POST['type']));
    } else {
        $error = 1;
        $err = "Room Type Cannot Be Empty";
    }

    if (isset($_POST['price']) && !empty($_POST['price'])) {
        $price = mysqli_real_escape_string($mysqli, trim($_POST['price']));
    } else {
        $error = 1;
        $err = "Room Price Cannot Be Empty";
    }

    if (isset($_POST['status']) && !empty($_POST['status'])) {
        $status = mysqli_real_escape_string($mysqli, trim($_POST['status']));
    } else {
        $error = 1;
        $err = "Room Status Cannot Be Empty";
    }

    if (isset($_POST['details']) && !empty($_POST['details'])) {
        $details = mysqli_real_escape_string($mysqli, trim($_POST['details']));
    } else {
        $error = 1;
        $err = "Room details Cannot Be Empty";
    }

    if (!$error) {
        $image = $_FILES['image']['name'];
        move_uploaded_file($_FILES["image"]["tmp_name"], "../public/uploads/rooms/" . $_FILES["image"]["name"]);
        $query = "UPDATE rooms SET number =?, type =?, price =?, status =?, details =?, image =? WHERE id =?";
        $stmt = $mysqli->prepare($query);
        $rc = $stmt->bind_param('sssssss', $number, $type, $price, $status, $details, $image, $id);
        $stmt->execute();
        if ($stmt) {
            $success = "Updated" && header("refresh:1; url=rooms.php");
        } else {
            //inject alert that task failed
            $info = "Please Try Again Or Try Later";
        }
    }
}

if (isset($_GET['Delete_Room'])) {
    /* Delete Room */
    $id = $_GET['Delete_Room'];
    $adn = "DELETE FROM rooms WHERE id =?";
    $stmt = $mysqli->prepare($adn);
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $stmt->close();
    if ($stmt) {
        $success = "Deleted" && header("refresh:1; url=rooms.php");
    } else {
        //inject alert that task failed
        $info = "Please Try Again Or Try Later";
    }
}

/* Bulk Import Rooms With Excel */

use keaHotelERP\DataSource;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

require_once '../config/DataSource.php';
$db = new DataSource();
$conn = $db->getConnection();
require_once '../vendor/autoload.php';

if (isset($_POST['upload'])) {
    $allowedFileType = [
        'application/vnd.ms-excel',
        'text/xls',
        'text/xlsx',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ];

    if (in_array($_FILES['file']['type'], $allowedFileType)) {
        $targetPath =
            '../public/uploads/sys_data/xls/' . $_FILES['file']['name'];
        move_uploaded_file($_FILES['file']['tmp_name'], $targetPath);

        $Reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();

        $spreadSheet = $Reader->load($targetPath);
        $excelSheet = $spreadSheet->getActiveSheet();
        $spreadSheetAry = $excelSheet->toArray();
        $sheetCount = count($spreadSheetAry);

        for ($i = 1; $i <= $sheetCount; $i++) {
            $id = '';
            if (isset($spreadSheetAry[$i][0])) {
                $id = mysqli_real_escape_string($conn, $spreadSheetAry[$i][0]);
            }

            $number = '';
            if (isset($spreadSheetAry[$i][1])) {
                $number = mysqli_real_escape_string(
                    $conn,
                    $spreadSheetAry[$i][1]
                );
            }

            $type = '';
            if (isset($spreadSheetAry[$i][2])) {
                $type = mysqli_real_escape_string(
                    $conn,
                    $spreadSheetAry[$i][2]
                );
            }

            $price = '';
            if (isset($spreadSheetAry[$i][3])) {
                $price = mysqli_real_escape_string($conn, $spreadSheetAry[$i][3]);
            }

            $status = '';
            if (isset($spreadSheetAry[$i][4])) {
                $status = mysqli_real_escape_string(
                    $conn,
                    $spreadSheetAry[$i][4]
                );
            }

            
            if (
                !empty($id) ||
                !empty($number) ||
                !empty($type) ||
                !empty($price) ||
                !empty($status)
            ) {
                $query ='INSERT INTO rooms (id, number, type, price, status) VALUES (?,?,?,?,?)';
                $paramType = 'sssss';
                $paramArray = [$id, $number, $type, $price, $status];
                $insertId = $db->insert($query, $paramType, $paramArray);
                if (!empty($insertId)) {
                    $err = 'Error Occured While Importing Data';
                } else {
                    $success = 'Room Data Imported';
                }
            }
        }
    } else {
        $info = 'Invalid File Type. Upload Excel File.';
    }
}


require_once("../partials/head.php");
?>

<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
    <div class="wrapper">
        <!-- Navbar -->
        <?php require_once("../partials/admin_nav.php"); ?>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <?php require_once("../partials/admin_sidebar.php"); ?>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <section class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1>Quản lý phòng</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="#">Trang chủ</a></li>
                                <li class="breadcrumb-item"><a href="dashboard.php">Bảng điều khiển</a></li>
                                <li class="breadcrumb-item active">Phòng</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </section>

            <section class="content">
                <div class="container-fluid">
                    <div class="text-right mb-3">
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#import_modal">Nhập phòng</button>
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#add-room">Thêm phòng</button>
                    </div>
                    <!-- Add  Modal -->
                    <div class="modal fade" id="add-room">
                        <div class="modal-dialog  modal-xl">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title">Thêm phòng mới</h4>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <form method="POST" enctype="multipart/form-data">
                                        <div class="form-row mb-4">
                                            <div style="display:none" class="form-group col-md-6">
                                                <label for="inputEmail4">Id</label>
                                                <input type="text" name="id" value="<?php echo $ID; ?>" class="form-control">
                                                <input type="text" name="number" class="form-control">
                                            </div>
                                        </div>
                                        <div class="form-row mb-4">
                                            <div class="form-group col-md-6">
                                                <label for="inputEmail4">Mã phòng</label>
                                                <input required type="text" value="<?php echo $a; ?>-<?php echo $b; ?>" name="number" class="form-control">
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label for="inputEmail4">Loại phòng</label>
                                                <select class='form-control' name="type" id="">
                                                    <option selected>Chọn loại phòng</option>
                                                    <option>Phòng đơn</option>
                                                    <option>Phòng đôi</option>
                                                    <option>Phòng deluxe</option>
                                                    <option>Phòng thường lớn</option>
                                                    <option>Phòng penthouse</option>
                                                    <option>Phòng tổng thống</option>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-4">
                                                <label for="inputEmail4">Trạng thái phòng</label>
                                                <select class='form-control' name="status" id="">
                                                    <option selected>Trống</option>
                                                    <option>Đã có khách</option>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-4">
                                                <label for="exampleInputFile">Ảnh phòng</label>
                                                <div class="input-group">
                                                    <div class="custom-file">
                                                        <input required name="image" accept=".png,.jpg" type="file" class="custom-file-input" id="exampleInputFile">
                                                        <label class="custom-file-label" for="exampleInputFile">Choose file</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group col-md-4">
                                                <label for="inputEmail4">Giá phòng</label>
                                                <input required type="text" name="price" class="form-control">
                                            </div>
                                        </div>

                                        <div class="form-row mb-4">
                                            <div class="form-group col-md-12">
                                                <label for="inputAddress">Chi tiết phòng</label>
                                                <textarea name="details" rows="6" class="form-control"></textarea>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <button type="submit" name="Add_Room" class="btn btn-primary">Thêm phòng</button>
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer justify-content-between">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End  Modal -->

                    <!-- Import Rooms -->
                    <div class="modal fade" id="import_modal">
                        <div class="modal-dialog  modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="text-center">
                                        Cho phép dạng: XLS, XLSX.
                                        <a class="text-primary" target="_blank" href="../public/templates/Rooms.xlsx">Download</a> A Sample File.
                                    </h4>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <form method="post" enctype="multipart/form-data" role="form">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="form-group col-md-12">
                                                    <label for="exampleInputFile">Select File</label>
                                                    <div class="input-group">
                                                        <div class="custom-file">
                                                            <input required name="file" accept=".xls,.xlsx" type="file" class="custom-file-input" id="exampleInputFile">
                                                            <label class="custom-file-label" for="exampleInputFile">Choose file</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <button type="submit" name="upload" class="btn btn-primary">Upload File</button>
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer justify-content-between">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End Import -->
                    <hr>
                    <div class="col-12">
                        <table id="dt-1" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Số phòng</th>
                                    <th>Loại phòng</th>
                                    <th>Trạng thái phòng</th>
                                    <th>Giá phòng</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $ret = "SELECT * FROM `rooms` ";
                                $stmt = $mysqli->prepare($ret);
                                $stmt->execute();
                                $res = $stmt->get_result();
                                while ($rooms = $res->fetch_object()) {
                                ?>
                                    <tr>
                                        <td><?php echo $rooms->number; ?></td>
                                        <td><?php echo $rooms->type; ?></td>
                                        <td>
                                            <?php
                                            if ($rooms->status == 'Occupied' || $rooms->status == 'Đã có khách') {
                                                echo "<span class='badge bg-danger'>Đã có khách</span>";
                                            } else {
                                                echo "<span class='badge bg-warning'>Trống</span>";
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo number_format($rooms->price); ?> VND</td>
                                        <td>
                                            <a class="badge bg-success" data-toggle="modal" href="#view-<?php echo $rooms->id; ?>"> <i class="fas fa-eye"></i> Xem </a>
                                            <!-- View Modal -->
                                            <div class="modal fade" id="view-<?php echo $rooms->id; ?>">
                                                <div class="modal-dialog modal-xl">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h4 class="modal-title"><?php echo $rooms->number; ?> - Chi tiết</h4>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Đóng">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body text-center">
                                                            <?php
                                                            if ($rooms->image == '') {
                                                                echo "<img src='../public/uploads/sys_logo/logo.png'  class='text-center img-fluid ' alt='Hình ảnh phòng'>";
                                                            } else {
                                                                echo "<img src='../public/uploads/rooms/$rooms->image'  class=' img-fluid ' alt='Hình ảnh phòng'>";
                                                            }
                                                            ?>
                                                        </div>
                                                        <div class="card-body">
                                                            <table class="table table-sm">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Số phòng</th>
                                                                        <th>Giá phòng</th>
                                                                        <th>Loại phòng</th>
                                                                        <th>Trạng thái</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <tr>
                                                                        <td>
                                                                            <div class="td-content"><span class="badge badge-success"><?php echo $rooms->number; ?></span></div>
                                                                        </td>
                                                                        <td>
                                                                            <?php echo number_format($rooms->price); ?> VND
                                                                        </td>
                                                                        <td>
                                                                            <?php echo $rooms->type; ?>
                                                                        </td>
                                                                        <td>
                                                                            <?php
                                                                            if ($rooms->status == 'Occupied' || $rooms->status == 'Đã có khách') {
                                                                                echo "<span class='badge bg-danger'>Đã có khách</span>";
                                                                            } else {
                                                                                echo "<span class='badge bg-warning'>Trống</span>";
                                                                            }
                                                                            ?>
                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                            <br>
                                                            <h4 class="text-center">Chi tiết phòng / Đặc điểm nổi bật</h4>
                                                            <p>
                                                                <?php echo $rooms->details; ?>
                                                            </p>
                                                        </div>
                                                        <div class="modal-footer justify-content-between">
                                                            <button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- End View Modal -->

                                            <?php
                                            if ($rooms->status == 'Occupied' || $rooms->status == 'Đã có khách') {
                                                // Không cho phép cập nhật/xóa khi phòng đã có khách
                                            } else {
                                                echo "<a class='badge bg-primary' href='#update-$rooms->id' data-toggle='modal'> <i class='fas fa-edit'></i> Cập nhật </a>";
                                                // Nút xóa
                                                echo "<a class='badge bg-danger' data-toggle='modal' href='#delete-$rooms->id'><i class='fas fa-trash'></i> Xóa</a>";
                                            }
                                            ?>
                                            <!-- Update Modal -->
                                            <div class="modal fade" id="update-<?php echo $rooms->id; ?>">
                                                <div class="modal-dialog modal-xl">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h4 class="modal-title">Cập nhật phòng: <?php echo $rooms->number; ?></h4>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <form method="POST" enctype="multipart/form-data">
                                                                <div class="form-row mb-4">
                                                                    <div class="form-group col-md-6">
                                                                        <label for="inputEmail4">Mã phòng</label>
                                                                        <input required type="text" value="<?php echo $rooms->number; ?>" name="number" class="form-control">
                                                                        <!-- Hide This -->
                                                                        <input required type="hidden" value="<?php echo $rooms->id; ?>" name="id" class="form-control">
                                                                    </div>
                                                                    <div class="form-group col-md-6">
                                                                        <label for="inputEmail4">Loại phòng</label>
                                                                        <select class='form-control ' name="type" id="">
                                                                            <option><?php echo $rooms->type; ?></option>
                                                                            <option>Phòng đơn</option>
                                                                            <option>Phòng đôi</option>
                                                                            <option>Phòng deluxe</option>
                                                                            <option>Phòng thường lớn</option>
                                                                            <option>Phòng penthouse</option>
                                                                            <option>Phòng tổng thống</option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="form-group col-md-4">
                                                                        <label for="inputEmail4">Trạng thái phòng</label>
                                                                        <select class='form-control ' name="status" id="">
                                                                            <option selected><?php echo $rooms->status; ?></option>
                                                                            <option>Trống</option>
                                                                            <option>Đã có khách</option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="form-group col-md-4">
                                                                        <label for="exampleInputFile">Ảnh phòng</label>
                                                                        <div class="input-group">
                                                                            <div class="custom-file">
                                                                                <input required name="image" accept=".png,.jpg" type="file" class="custom-file-input" id="exampleInputFile">
                                                                                <label class="custom-file-label" for="exampleInputFile">Choose file</label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="form-group col-md-4">
                                                                        <label for="inputEmail4">Giá phòng</label>
                                                                        <input required type="text" value="<?php echo $rooms->price; ?>" name="price" class="form-control">
                                                                    </div>
                                                                </div>

                                                                <div class="form-row mb-4">
                                                                    <div class="form-group col-md-12">
                                                                        <label for="inputAddress">Chi tiết phòng</label>
                                                                        <textarea name="details" rows="8" class="form-control"><?php echo $rooms->details; ?></textarea>
                                                                    </div>
                                                                </div>
                                                                <div class="text-right">
                                                                    <button type="submit" name="Update_Room" class="btn btn-primary">Cập nhật phòng</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                        <div class="modal-footer justify-content-between">
                                                            <button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- End Update Modal -->

                                            <!-- Delete Modal -->
                                            <div class="modal fade" id="delete-<?php echo $rooms->id; ?>" tabindex="-1" role="dialog" aria-labelledby="deleteLabel-<?php echo $rooms->id; ?>" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="deleteLabel-<?php echo $rooms->id; ?>">XÁC NHẬN</h5>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Đóng">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body text-center text-danger">
                                                            <h4>Xóa phòng <?php echo $rooms->number; ?>?</h4>
                                                            <br>
                                                            <button type="button" class="btn btn-success" data-dismiss="modal">Không</button>
                                                            <a href="rooms.php?Delete_Room=<?php echo $rooms->id; ?>" class="btn btn-danger">Xóa</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- End Delete Modal -->
                                        </td>
                                    </tr>
                                <?php } ?>
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