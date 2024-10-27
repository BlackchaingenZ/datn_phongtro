<?php
if (!defined('_INCODE')) die('Access Deined...');


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;


function layout($layoutName = 'header', $dir = '', $data = [])
{

    if (!empty($dir)) {
        $dir = '/' . $dir;
    }

    if (file_exists(_WEB_PATH_TEMPLATE . $dir . '/layouts/' . $layoutName . '.php')) {
        require_once _WEB_PATH_TEMPLATE . $dir . '/layouts/' . $layoutName . '.php';
    }
}

function sendMail($to, $subject, $content)
{
    //Create an instance; passing `true` enables exceptions
    $mail = new PHPMailer(true);

    try {
        //Server settings
        $mail->SMTPDebug = SMTP::DEBUG_OFF;                      //Enable verbose debug output
        $mail->isSMTP();                                            //Send using SMTP
        $mail->Host       = 'smtp.gmail.com';                     //Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
        $mail->Username   = 'phongtrothaonguyen@gmail.com';                     //SMTP username
        $mail->Password   = 'fjrbtpylmaeaskzt';                               //SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
        $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

        //Recipients
        $mail->setFrom('phongtrothaonguyen@gmail.com', 'Phòng Trọ Thảo Nguyên');
        $mail->addAddress($to, 'Quý khách');

        //Content
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';                             //Set email format to HTML
        $mail->Subject = $subject;
        $mail->Body    = $content;

        return  $mail->send();
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

//Kiểm tra phương thức POST
function isPost()
{
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        return true;
    }

    return false;
}

//Kiểm tra phương thức GET
function isGet()
{
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        return true;
    }

    return false;
}

//Lấy giá trị phương thức POST, GET
function getBody()
{

    $bodyArr = [];

    if (isGet()) {
        //Xử lý chuỗi trước khi hiển thị ra
        //return $_GET;
        /*
         * Đọc key của mảng $_GET
         *
         * */
        if (!empty($_GET)) {
            foreach ($_GET as $key => $value) {
                $key = strip_tags($key);
                if (is_array($value)) {
                    $bodyArr[$key] = filter_input(INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_REQUIRE_ARRAY);
                } else {
                    $bodyArr[$key] = filter_input(INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS);
                }
            }
        }
    }

    if (isPost()) {
        if (!empty($_POST)) {
            foreach ($_POST as $key => $value) {
                $key = strip_tags($key);
                if (is_array($value)) {
                    $bodyArr[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_REQUIRE_ARRAY);
                } else {
                    $bodyArr[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
                }
            }
        }
    }

    return $bodyArr;
}

//Kiểm tra email
function isEmail($email)
{
    $checkEmail = filter_var($email, FILTER_VALIDATE_EMAIL);
    return $checkEmail;
}

//Kiểm tra số nguyên
function isNumberInt($number, $range = [])
{
    /*
     * $range = ['min_range'=>1, 'max_range'=>20];
     *
     * */
    if (!empty($range)) {
        $options = ['options' => $range];
        $checkNumber = filter_var($number, FILTER_VALIDATE_INT, $options);
    } else {
        $checkNumber = filter_var($number, FILTER_VALIDATE_INT);
    }

    return $checkNumber;
}

//Kiểm tra số thực
function isNumberFloat($number, $range = [])
{
    /*
     * $range = ['min_range'=>1, 'max_range'=>20];
     *
     * */
    if (!empty($range)) {
        $options = ['options' => $range];
        $checkNumber = filter_var($number, FILTER_VALIDATE_FLOAT, $options);
    } else {
        $checkNumber = filter_var($number, FILTER_VALIDATE_FLOAT);
    }

    return $checkNumber;
}

//Kiểm tra số điện thoại (0123456789 - Bắt đầu bằng số 0, nối tiếp là 9 số)
// function isPhone($phone){

//     $checkFirstZero = false;

//     if ($phone[0]=='0'){
//         $checkFirstZero = true;
//         $phone = substr($phone, 1);
//     }

//     $checkNumberLast = false;

//     if (isNumberInt($phone) && strlen($phone)==9){
//         $checkNumberLast = true;
//     }

//     if ($checkFirstZero && $checkNumberLast){
//         return true;
//     }

//     return false;
// }

//Hàm tạo thông báo
function getMsg($msg, $type = 'suc')
{
    if (!empty($msg)) {
        echo '<div class="' . $type . '">';
        if ($type === 'suc') {
?>
            <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/check.png" alt="">
        <?php
        } elseif ($type === 'err') {
        ?>
            <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/error.png" alt="">
<?php
        }
        echo $msg;
        echo '</div>';
    }
}

//Hàm chuyển hướng
/**
 * Redirects the user to a specified path within the web host root.
 *
 * @param string $path The relative path to redirect to, default is 'index.php'.
 * 
 * @return void This function does not return a value. It sends a header to redirect the user and exits the script.
 */
function redirect($path = 'index.php')
{
    $url = _WEB_HOST_ROOT . '/' . $path;
    header("Location: $url");
    exit;
}


//Hàm thông báo lỗi
function form_error($fieldName, $errors, $beforeHtml = '', $afterHtml = '')
{
    return (!empty($errors[$fieldName])) ? $beforeHtml . reset($errors[$fieldName]) . $afterHtml : null;
}

//Hàm hiển thị dữ liệu cũ
function old($fieldName, $oldData, $default = null)
{
    return (!empty($oldData[$fieldName])) ? $oldData[$fieldName] : $default;
}

//Kiểm tra trạng thái đăng nhập của Admin
function isLogin()
{
    $checkLogin = false;
    if (getSession('loginToken')) {
        $tokenLogin = getSession('loginToken');

        $queryToken = firstRaw("SELECT user_id FROM login_token WHERE token='$tokenLogin'");

        if (!empty($queryToken)) {
            //$checkLogin = true;
            $checkLogin = $queryToken;
        } else {
            removeSession('loginToken');
        }
    }

    return $checkLogin;
}

//Lấy thông tin user
function getUserInfo($user_id)
{
    $info = firstRaw("SELECT * FROM users WHERE id=$user_id");
    return $info;
}


// activeMenuSidebar
function activeMenuSidebar($module)
{
    if (getBody()['module'] == $module) {
        return true;
    }
    return false;
}

// GetLink
function getLinkAdmin($module, $action = '', $param = [])
{
    $url = _WEB_HOST_ROOT;
    $url = $url . '?module=' . $module;
    if (!empty($action)) {
        $url = $url . '&action=' . $action;
    }

    if (!empty($param)) {
        $paramString = http_build_query($param);
        $url = $url . '&' . $paramString;
    }
    return $url;
}

// // Tạo URL quay về trang add
// $linkToAdd = getLinkAdmin('equipment', 'listequipment');

// Format Date
function getDateFormat($strDate, $format)
{
    $dateObject = date_create($strDate);
    if (!empty($dateObject)) {
        return date_format($dateObject, $format);
    }
    return false;
}

// Check font-awesome
function isFontIcon($input)
{
    if (strpos($input, '<i class="') != false) {
        return true;
    }
    return false;
}

// Hàm kiểm tra trang hiện tại có phải trrang admin không
function isAdmin()
{
    if (!empty($_SERVER['PHP_SELF'])) {
        $currentFile = $_SERVER['PHP_SELF'];
        $dirFile = dirname($currentFile);
        $baseNameDir = basename($dirFile);
        if (trim($baseNameDir) == 'admin') {
            return true;
        }
    }
    return false;
}

function getPath()
{
    $path = '';
    if (!empty($_SERVER['QUERY_STRING'])) {
        $path = '?' . trim($_SERVER['QUERY_STRING']);
    }
    return $path;
}


function loadErrors($name = '404')
{
    $pathErrors = _WEB_PATH_ROOT . '/modules/errors/' . $name . '.php';
    require_once $pathErrors;
    die();
}

// in ra mã đơn hàng tự động
function generateInvoiceCode($length = 5)
{
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    $charactersLength = strlen($characters);
    $randomString = '';

    for ($i = 0; $i < $length; $i++) {
        $randomIndex = random_int(0, $charactersLength - 1);
        $randomString .= $characters[$randomIndex];
    }

    return $randomString;
}

// check trạng thái hợp đồng
function getContractStatus($endDate)
{
    $currentDate = new DateTime();
    $contractEndDate = new DateTime($endDate);
    $interval = $currentDate->diff($contractEndDate); // Tính khoảng cách giữa 2 ngày
    $daysLeft = (int)$interval->format('%R%a'); // chuyển khoảng cách ngày thành số ngày

    if ($daysLeft < 0) {
        return "Đã hết hạn";
    } elseif ($daysLeft > 0 && $daysLeft <= 30) {
        return "Sắp hết hạn";
    } else {
        return "Trong thời hạn";
    }
}

// truy vấn lấy dữ liệu của equipment xem có đang liên lết với room nào không
function getRow($sql)
{
    global $pdo; // Sử dụng kết nối PDO toàn cục
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC); // Trả về một dòng dưới dạng mảng liên kết
}

// Hàm thực hiện xóa thiết bị khỏi phòng, với đối tượng $pdo truyền vào
function deleteEquipmentFromRoom($pdo, $roomId, $equipmentId)
{
    // Truy vấn để xóa thiết bị khỏi phòng dựa trên room_id và equipment_id
    $stmt = $pdo->prepare("DELETE FROM equipment_room WHERE room_id = :roomId AND equipment_id = :equipmentId");
    $stmt->bindParam(':roomId', $roomId);
    $stmt->bindParam(':equipmentId', $equipmentId);

    return $stmt->execute(); // Trả về true nếu xóa thành công
}

// Hàm thực hiện xóa khu vực khỏi phòng, với đối tượng $pdo truyền vào
function deleteAreaFromRoom($pdo, $roomId, $areaId)
{
    // Truy vấn để xóa khu vực khỏi phòng dựa trên room_id và equipment_id
    $stmt = $pdo->prepare("DELETE FROM area_room WHERE room_id = :roomId AND area_id = :areaId");
    $stmt->bindParam(':roomId', $roomId);
    $stmt->bindParam(':areaId', $areaId);

    return $stmt->execute(); // Trả về true nếu xóa thành công
}

// Hàm thực hiện xóa loại giá khỏi phòng, với đối tượng $pdo truyền vào
function deleteCostFromRoom($pdo, $roomId, $costId)
{
    // Truy vấn để xóa thiết bị khỏi phòng dựa trên room_id và cost_id
    $stmt = $pdo->prepare("DELETE FROM cost_room WHERE room_id = :roomId AND cost_id = :costId");
    $stmt->bindParam(':roomId', $roomId);
    $stmt->bindParam(':costId', $costId);

    return $stmt->execute(); // Trả về true nếu xóa thành công
}

// kiểm tra thiết bị có ở trong phòng không(lấy toàn bộ chưa check)
function checkEquipmentInRoom($pdo, $roomId)
{
    // Truy vấn để kiểm tra xem phòng có thiết bị không
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM equipment_room WHERE room_id = :roomId");
    $stmt->bindParam(':roomId', $roomId);
    $stmt->execute();

    // Trả về số lượng thiết bị trong phòng
    return $stmt->fetchColumn();
}
function checkAreaInRoom($pdo, $roomId)
{
    // Truy vấn để kiểm tra xem phòng có thiết bị không
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM area_room WHERE room_id = :roomId");
    $stmt->bindParam(':roomId', $roomId);
    $stmt->execute();

    // Trả về số lượng thiết bị trong phòng
    return $stmt->fetchColumn();
}
// kiểm tra bảng giá có ở trong phòng hay không
function checkcostInRoom($pdo, $roomId)
{
    // Truy vấn để kiểm tra xem phòng có thiết bị không
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM cost_room WHERE room_id = :roomId");
    $stmt->bindParam(':roomId', $roomId);
    $stmt->execute();

    // Trả về số lượng thiết bị trong phòng
    return $stmt->fetchColumn();
}

// kiểm tra xem thiết bị đấy có tồn tại trong phòng không
function checkAreaInRoomById($pdo, $roomId, $areaId)
{
    try {
        // Câu truy vấn kiểm tra thiết bị có trong phòng hay không
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM area_room WHERE room_id = :room_id AND area_id = :area_id");
        $stmt->bindParam(':room_id', $roomId, PDO::PARAM_INT);
        $stmt->bindParam(':area_id', $areaId, PDO::PARAM_INT);
        $stmt->execute();

        // Lấy kết quả đếm
        $count = $stmt->fetchColumn();

        // Nếu kết quả lớn hơn 0, thiết bị tồn tại trong phòng
        return $count > 0;
    } catch (PDOException $e) {
        // Xử lý lỗi nếu có vấn đề với cơ sở dữ liệu
        return false;
    }
}

// kiểm tra xem thiết bị đấy có tồn tại trong phòng không
function checkEquipmenntInRoomById($pdo, $roomId, $equipmentId)
{
    try {
        // Câu truy vấn kiểm tra thiết bị có trong phòng hay không
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM equipment_room WHERE room_id = :room_id AND equipment_id = :equipment_id");
        $stmt->bindParam(':room_id', $roomId, PDO::PARAM_INT);
        $stmt->bindParam(':equipment_id', $equipmentId, PDO::PARAM_INT);
        $stmt->execute();

        // Lấy kết quả đếm
        $count = $stmt->fetchColumn();

        // Nếu kết quả lớn hơn 0, thiết bị tồn tại trong phòng
        return $count > 0;
    } catch (PDOException $e) {
        // Xử lý lỗi nếu có vấn đề với cơ sở dữ liệu
        return false;
    }
}

function checkCostInRoomById($pdo, $roomId, $costId)
{
    try {
        // Câu truy vấn kiểm tra bảng giá có trong phòng hay không
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM cost_room WHERE room_id = :room_id AND cost_id = :cost_id");
        $stmt->bindParam(':room_id', $roomId, PDO::PARAM_INT);
        $stmt->bindParam(':cost_id', $costId, PDO::PARAM_INT);
        $stmt->execute();

        // Lấy kết quả đếm
        $count = $stmt->fetchColumn();

        // Nếu kết quả lớn hơn 0, thiết bị tồn tại trong phòng
        return $count > 0;
    } catch (PDOException $e) {
        // Xử lý lỗi nếu có vấn đề với cơ sở dữ liệu
        return false;
    }
}


// Hàm thực hiện truy vấn thông tin cost
function executeResult($query, $params = [])
{
    try {
        // Sử dụng kết nối cơ sở dữ liệu có sẵn, giả sử nó được lưu trong biến $pdo
        global $pdo;

        // Chuẩn bị và thực hiện truy vấn
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);

        // Lấy tất cả kết quả truy vấn
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Lỗi truy vấn cơ sở dữ liệu: " . $e->getMessage();
        return [];
    }
}
