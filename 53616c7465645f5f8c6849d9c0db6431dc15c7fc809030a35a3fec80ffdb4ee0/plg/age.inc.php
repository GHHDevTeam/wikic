<?php
// $Id: age.inc.php,v 1.0 2003/06/08 00:00:00 upk Exp $

/*
 * age.inc.php
 * License: GPL
 * Author: Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * Last-Update: 2003-06-08
 *
 */

function plugin_age_inline() {
  list($y,$m,$d) = func_get_args();
  return age($y,$m,$d);
}

function age($y,$m,$d) {
  $ny = date("Y",UTIME);
  $nm = date("m",UTIME);
  $nd = date("d",UTIME);

  $md  = $m*100 +$d;
  $nmd = $nm*100+$nd;
  $age = $ny - $y;

  if ($nmd >= $md)
    return $age;
  else
    return $age-1;
}

?>