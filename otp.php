<?php
session_start();

if ($_SESSION)
{


if ($_POST){
    $otp = $_POST['otp'];
    $cEmail = $_POST['mail'];
    $otpError="";

    //  <------------------------GET IP ADDR----------------------------------->
    function getIPAddress() {
      //whether ip is from the share internet
       if(!empty($_SERVER['HTTP_CLIENT_IP'])) {
                  $ip = $_SERVER['HTTP_CLIENT_IP'];
          }
      //whether ip is from the proxy
      elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                  $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
       }
  //whether ip is from the remote address
      else{
               $ip = $_SERVER['REMOTE_ADDR'];
       }
       return $ip;
  }
  $ip = getIPAddress();
    //  <------------------------------------------------------------------------>


    //  <------------------------GET BROWSER NAME-------------------------------->
      function get_browser_name($user_agent)
  {
      if (strpos($user_agent, 'Opera') || strpos($user_agent, 'OPR/')) return 'Opera';
      elseif (strpos($user_agent, 'Edge')) return 'Edge';
      elseif (strpos($user_agent, 'Chrome')) return 'Chrome';
      elseif (strpos($user_agent, 'Safari')) return 'Safari';
      elseif (strpos($user_agent, 'Firefox')) return 'Firefox';
      elseif (strpos($user_agent, 'MSIE') || strpos($user_agent, 'Trident/7')) return 'Internet Explorer';

      return 'Other';
  }
  $browser = get_browser_name($_SERVER['HTTP_USER_AGENT']);
    //  <------------------------------------------------------------------------>

    //MYSQL Info
    $dbuser="root";
    $passwd= "Cyberark1";
    $host="localhost";
    $db="caUnlocker";

    //MYSQL Connection
    $conn = mysqli_connect($host, $dbuser, $passwd, $db) or die("Veritabanı bağlantısı yapılamadı !");
    mysqli_select_db($conn,$db)or die("Veritabanı bağlantısı yapılamadı !");
    mysqli_query($conn,"SET NAMES 'utf8'");

    set_time_limit(30);
    error_reporting(E_ALL);
    ini_set('error_reporting', E_ALL);
    ini_set('display_errors',1);

    $checkOTP = mysqli_query($conn, "SELECT OTP FROM caUnlocker.caOTP
    WHERE eMail='$cEmail'");


    $hede = mysqli_fetch_array($checkOTP);
    $dOTP = ($hede['OTP']);



    if($dOTP == $otp) {

        $serviceUser = "cyberark.serviceuser";
        $servicePasswd = "password";
        $destUser = trim($cEmail,'@acme.com');
        $logonUrl = "https://PVWAAddress/PasswordVault/api/Auth/cyberark/Logon";
        $logoffUrl = "https://PVWAAddress/PasswordVault/api/auth/Logoff";
        $getUserIdURL = "https://PVWAAddress/PasswordVault/api/Users?filter=userType&search=". $destUser ."&extendedDetails=false";


        $data = array("username" => $serviceUser, "password" => $servicePasswd);
        $data_string = json_encode($data);

          $ch = curl_init($logonUrl);
          curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
          curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($ch, CURLOPT_HTTPHEADER, array(
          'Content-Type: application/json',
          'Content-Length: ' . strlen($data_string)));


          $tokenUnStrim = curl_exec($ch);
          $tokenUnStrim = ltrim($tokenUnStrim, '"');
          $token = rtrim($tokenUnStrim, '"');



          $ch2 = curl_init($getUserIdURL);
          curl_setopt($ch2, CURLOPT_CUSTOMREQUEST, "GET");
          curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($ch2, CURLOPT_HTTPHEADER, array(
          'Content-Type: application/json',
          'Authorization: ' . $token));

          $result_get = curl_exec($ch2);
          $ARRAY = json_decode($result_get,true);
          curl_close($ch2);
          $caUserID = ($ARRAY['Users'][0]['id']);

          $activateUserUrl = "https://PVWAAddress/PasswordVault/api/Users/" . $caUserID . "/Activate";


          $ch3 = curl_init($activateUserUrl);
          curl_setopt($ch3, CURLOPT_CUSTOMREQUEST, "POST");
          curl_setopt($ch3, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($ch3, CURLOPT_HTTPHEADER, array(
          'Content-Type: application/json',
          'Content-Length: 0',
          'Authorization: ' . $token));

          $result_get3 = curl_exec($ch3);
          $ARRAY3 = json_decode($result_get3,true);
          curl_close($ch3);
          if (empty($ARRAY3)){

            mysqli_query($conn,"DELETE FROM caOTP WHERE eMail='$cEmail' ") or die(mysqli_error($conn));
            mysqli_query($conn,"INSERT INTO unlockHistory(eMail,OTP,kullaniciOTP,IP,Browser,durum) values('$cEmail', '$dOTP','$otp','$ip','$browser','Basarili')");
            unset($_SESSION['loggedin']);
	          unset($_SESSION['username']);

            mysqli_close($conn);

          }

          elseif (in_array('PASWS261E',$ARRAY3)) {
            $genError = "Böyle bir kullanıcı yok! ya da bir şeyler ters gitti.";
	    mysqli_close($conn);
          }

          else {
            print_r($ARRAY3);
	    mysqli_close($conn);
          }

        }
        else {
            $otpError = "Girilen OTP eşleşmedi!";
	    mysqli_query($conn,"INSERT INTO unlockHistory(eMail,OTP,IP,Browser,durum) values('$cEmail', '$dOTP','$ip','$browser','Basarisiz')");
	    mysqli_close($conn);
        }
}


?>

<!doctype html>
<html lang="tr">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="pragma" content="no-cache" />

    <!-- Style -->
    <link rel="stylesheet" href="fonts/icomoon/style.css">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <!-- Style -->
    <link rel="stylesheet" href="css/style.css">

    <title>Unlocker</title>
  </head>
  <body>

  <div class="content">
    <div class="container">
      <div class="row align-items-stretch justify-content-center no-gutters">
        <div class="col-md-7">
          <div class="form h-100 contact-wrap p-5">
            <h3 class="text-center">Unlocker</h3>
            <form class="mb-5" action="otp.php" method="POST" id="unlockForm" name="unlockForm">
              <div class="row">


              <div class="col-md-6 form-group mb-3">
                  <label for="" class="col-form-label">Mail *</label>
                  <input type="text" class="form-control" name="mail" id="mail" value="<?php
                  if (isset($_GET['eMail'])){
                  echo $_GET['eMail'];}
                  ?>" placeholder="" required>
                </div>


                <div class="col-md-6 form-group mb-3">
                  <label for="" class="col-form-label">OTP *</label>
                  <input type="password" class="form-control" name="otp" id="otp" value="" placeholder="" required>
                </div>

                <div class="col-md-6 form-group mb-3">

                </div>
              </div>

              <div class="row">
                <div class="col-md-12 form-group mb-3">
                <p><center><small>Mail kutunuza gelen OTP'yi giriniz.</small></center></p>
                </div>
              </div>

              <div class="row justify-content-center">
                <div class="col-md-5 form-group text-center">
                  <input type="submit" value="Submıt" class="btn btn-block btn-primary rounded-0 py-2 px-4">
                  <span class="submitting"></span>
                </div>
              </div>
            </form>
            <div></div>

              <div>
                <?php
                if ($_POST and empty($ARRAY3) and empty($otpError)){?>
                <div class="alert alert-success" role="alert">
                  <?php echo $destUser . " hesabının kilidi kaldırıldı.";
                  ?>
                </div>
                <?php
              }   elseif ($_POST and !empty($ARRAY3)){ ?>
                <div class="alert alert-danger" role="alert">
                  <?php echo ("Bir şeyler ters gitti!"); ?>
                </div>
                <?php
              } elseif (!empty($otpError)) { ?>

                <div class="alert alert-danger" role="alert">
                  <?php echo ("Geçersiz OTP!"); ?>
                </div>
                <?php
              }
                ?>


          </div>

        </div>
        <p><center><small>Bilgi Güvenliği infosec-IAM@sahibinden.com </small></center></p>
      </div>

    </div>

  </div>


    <script src="js/jquery-3.3.1.min.js"></script>
    <script src="js/popper.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/jquery.validate.min.js"></script>
    <script src="js/main.js"></script>


  </body>
</html>

<?php }
else
{
	echo "Aktif oturum yok giriş yapmak için: <br><a href='index.php'><b>Unlocker</b></a>";
}
?>