<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">

    <title>MetaTrial</title>

<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">

<!-- Optional theme -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">


<link href="/css/style.css" rel="stylesheet">
  </head>

  <body>

    <div class="container home">
<img src="/images/logo.png" width="350" class="home-logo" />
<h2 class="subtitle">MetaTrial is the best way in the world to see the data you want.<br>
Try it out below.</h2>

<form action='/search' method='post' class="home-form">
{!! csrf_field() !!}
  <input type="text" name="condition" class="home-text" placeholder="Condition"><br>
  <input type="text" name="intervention" class="home-text" placeholder="Interventions"><br>
  <input type="text" name="outcome" class="home-text" placeholder="Outcome"><br>
<button type="button" class="home-btn">
<div class="glyphicon glyphicon-search pull-left" aria-hidden="true"></div> <span class="home-btn-text">Search</span>
</button>
</form>
    </div>


<div class="container main">
<h1 class="main-h1">You are comparing the effect of <strong class="intervention1">Apixaban</strong> and <strong class="intervention2">Warfarin</strong><br>
on the incidence of <strong class="outcome">Bleeding</strong><br>
in patients with <strong class="condition">Atrial Fibrillation</strong>.</h1>

</div>

<script src="/js/jquery.js"></script>
<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>

<script>
$(".home-btn").click(function(e) {
  console.log('Submitting form');
  $(".home-btn-text").text("Loading...");
  $(".home-text[name='condition']").delay(800).animate({
    width:"0",
    opacity:"0",
  }, 1000, function() {});
  $(".home-text[name='interventions']").delay(1200).animate({
    width:"0",
    opacity:"0",
  }, 1000, function() {});
  $(".home-text[name='outcome']").delay(1600).animate({
    width:"0",
    opacity:"0",
  }, 1000, function() {});
  $(".home-btn").delay(2000).animate({
    width:"0",
    opacity:"0",
  }, 500, function() {});
  $(".home-logo").delay(2200).animate({
    opacity:'toggle',
    height:'toggle',
  }, 800, function() {});
  $(".subtitle").delay(2100).animate({
    opacity:'toggle',
    height:'toggle',
  }, 800, function() {});
  $(".home-form").submit();

  $(".home").delay(2500).fadeOut();
  $(".main-h1").delay(2900).slideDown();

  

});



</script>



  </body>
</html>
