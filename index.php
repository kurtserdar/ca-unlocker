<?php
include "inc/engine.php";
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
            <form class="mb-5" action="inc/engine.php" method="POST" id="unlockForm" name="unlockForm">
              <div class="row">
                <div class="col-md-6 form-group mb-3">
                  <label for="" class="col-form-label">Username *</label>
                  <input type="text" class="form-control" name="username" id="username" placeholder="" required>
                </div>
                <div class="col-md-6 form-group mb-3">
                  <label for="" class="col-form-label">Password *</label>
                  <input type="password" class="form-control" name="password" id="password" placeholder="" required>
                </div>

                <div class="col-md-6 form-group mb-3">

                </div>
              </div>

              <div class="row">
                <div class="col-md-12 form-group mb-3">
                <p><center><small>Kilidini kaldırmak istediğiniz CyberArk hesap bilgilerini giriniz.<br>Eğer hata alırsanız bilgileriniz yanlış olabilir yada hesabınız domain'de de kilitli olabilir.</small></center></p>
                </div>
              </div>
              <div class="row justify-content-center">
                <div class="col-md-5 form-group text-center">
                  <input type="submit" value="Submıt" class="btn btn-block btn-primary rounded-0 py-2 px-4">
                  <span class="submitting"></span>
                </div>
              </div>
            </form>
            <div>

            </div>
            <div>
              <?php
              if ($_POST and $result_get){ ?>
               <div class="alert alert-success" role="alert">
                <?php echo ("Public SSH Key has been imported.<br>KEY ID: ");
                echo $ARRAY["AddUserAuthorizedKeyResult"]["KeyID"];
                ?>
              </div>
              <?php
            }   if ($_POST and strpos($result, 'PASWS013E') == true){ ?>
              <div class="alert alert-danger" role="alert">
                <?php echo ("Authentication failure!"); ?>
              </div>
              <?php
            }
              ?>

          </div>

        </div>
        <p><center><small>Bilgi Güvenliği infosec-IAM@acme.com </small></center></p>
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