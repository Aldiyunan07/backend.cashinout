<?php
function formatPrice($string)
{
    return str_replace(',','.', number_format($string));
}