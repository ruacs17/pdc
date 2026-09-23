<?php
function pageTitle(){
$completePage=$_SERVER['PHP_SELF'];
$pageTitle="";
$p = explode("/",$completePage);
$currentPage=$p[count($p)-1];
if( $currentPage=="dashboard.php" )
  $pageTitle=" | Dashboard";
else if( $currentPage=="voucher_list.php" )
  $pageTitle=" | Voucher List";
else if( $currentPage=="voucher_add.php" )
  $pageTitle=" | Voucher Add";
else if( $currentPage=="project_list.php" )
  $pageTitle=" | Project List";
else if( $currentPage=="po_list.php" )
  $pageTitle=" | P.O List";
else if( $currentPage=="po_add.php" )
  $pageTitle=" | P.O Add";
else if( $currentPage=="project_add.php" )
  $pageTitle=" | Project Add";
else if( $currentPage=="deduction.php" )
  $pageTitle=" | Deduction Category";
else if( $currentPage=="profile.php" )
  $pageTitle=" | Profile";
return $pageTitle;
}
?>