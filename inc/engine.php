<?php
session_start();


// --------------- OTP Function --------------->

function randomthing($size)
{
$characters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
$str = "";
while(strlen($str) < $size)
{
$str .= substr($characters, (rand() % strlen($characters)), 1);
}
return($str);
}


// Verify LDAP user and user's email function.
function verifyLdapUser($username,$password)
    {

        $DomainName="acme.com"; // name = domain
        $ldap_server="DC.acme.com"; // server = ldap://doman.co
		$ldaptree    = "OU=ACME,OU=Merkez,OU=Sahibinden,DC=acme,DC=com";
        // returns true when user/pass binds to LDAP/AD.
        $auth_user=$username."@".$DomainName;

        //Check to see if LDAP module is loaded.
       	if (extension_loaded('ldap')) {

           	if($connect=@ldap_connect($ldap_server)){

	        	//echo "connection ($ldap_server): ";
	            if($ldapbind=@ldap_bind($connect, $auth_user, $password)){

	            	$result = ldap_search($connect,$ldaptree, "(sAMAccountName=".$username.")") or die ("Error in search query: ".ldap_error($connect));

					$data = ldap_get_entries($connect, $result);

					// iterate over array and print data for each entry


					for ($i=0; $i<$data["count"]; $i++) {
						if(isset($data[$i]["mail"][0])) {
							$gEmail = $data[$i]["mail"][0];
						} else {
							echo "OTP gönderilecek email: Bulunamadı!<br /><br />";
						}


					}
	                // return true;

					$RandPass = randomthing(5);

					// MYSQL Config

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

					$oncedenKayitliMi = mysqli_query($conn, "SELECT * FROM caOTP
						WHERE eMail='$gEmail'");

					//unLock icin kullanilan Mail DB'de varsa OTP güncelle
					if(mysqli_fetch_array($oncedenKayitliMi) == true) {

					mysqli_query($conn,"UPDATE caOTP SET OTP='$RandPass' WHERE eMail='$gEmail' ") or die(mysqli_error($conn));

					}


					//unLock icin kullanilan Mail onceden DB'de yoksa ilk kaydi gerceklestir.
					else{

					mysqli_query($conn,"INSERT INTO caOTP(eMail,OTP) values('$gEmail', '$RandPass')");
					// if success -->
					}
					// Send OTP via email
					ini_set("SMTP","SMTPSERVERIP" );
					ini_set('sendmail_from', 'cyberark@acme.com');

					$Name = "CyberArk System";
					$email = "cyberark@acme.com";
					$recipient = $gEmail;
					$mail_body = "CyberArk hesabınızın kilidini kaldırmak için aşağıdaki OTP'yi giriniz:<br>OTP: " . $RandPass;
					$subject = "CyberArk System";
					$header = "Content-Type: text/html; charset=UTF-8";

					mail($recipient, $subject, $mail_body, $header);
					//mail($recipient, $subject, $mail_body, $header, '-f'.$email);


					// create session
					$_SESSION['loggedin'] = true;
					$_SESSION['username'] = $loginName;

					header("Location:../otp.php?eMail=$gEmail");


	            } else {
	               	//send error message - password incorrect
	               	echo "Girilen kullanıcı adı veya şife yanlış <BR>Bir ihtimal domainde de hesabınız kitli olabilir. İlk önce domain'den kilidinizi kaldırın. (EIT)";
	            	@ldap_close($connect);
	               	return false;
	            }
            }
        } else {
           	//send message - could not connect to domain
           	@ldap_close($connect);
           	return false;
        }
        // send message - ldap module not loaded

        @ldap_close($connect);
        return(false);
    }//end function verifyLdapUser



	if ($_POST){
		$username = $_POST['username'];
		$password = $_POST['password'];
	verifyLdapUser($username,$password);
}

?>