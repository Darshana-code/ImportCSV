<?php $var = range(1, 100); ?>
<?php foreach ($var as &$number)
{
    if($number % 3 == 0 &&  $number % 5 == 0)
    {
        echo "foobar";
    }
    elseif ($number % 5 == 0)
    {
        echo "bar";
    }
    elseif ($number % 3 == 0)
    {
        echo "foo";
    } else {
        echo "$number";
    }
    if ($number != 100) echo ", ";
}
?>