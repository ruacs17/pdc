<?php
function pageTitle(){
$completePage=$_SERVER['PHP_SELF'];
$pageTitle="";
$p = explode("/",$completePage);
$currentPage=$p[count($p)-1];
if( $currentPage=="member.php" )
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
else if( $currentPage=="supplier.php" )
  $pageTitle=" | Supplier / Payee";
else if( $currentPage=="voucher_type_list.php" )
  $pageTitle=" | Voucher Type";
else if( $currentPage=="user_list.php" )
  $pageTitle=" | User Management";
else if( $currentPage=="admin-equipment.php" )
  $pageTitle=" | Equipment";
else if( $currentPage=="admin_equipment_leasing.php" )
  $pageTitle=" | Equipment Leasing";
else if( $currentPage=="admin-inhouse-material.php" )
  $pageTitle=" | Warehouse Stocks";
return $pageTitle;
}
?>