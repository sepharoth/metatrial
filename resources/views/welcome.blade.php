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
<h2 class="subtitle">MetaTrial searches the US Clinical Trials database<br> to give you an at-a-glance answer to your research question.</h2>

<form action='/search' method='post' class="home-form">
  <input type="text" name="condition" class="home-text" placeholder="Condition"><br>
  <input type="text" name="intervention" class="home-text" placeholder="Interventions"><br>
  <input type="text" name="outcome" class="home-text" placeholder="Outcome"><br>
<button type="button" class="home-btn">
<div class="glyphicon glyphicon-search pull-left" aria-hidden="true"></div> <span class="home-btn-text">Search</span>
</button>
</form>
    </div>


<div class="container main">
<h1 class="main-h1">You are comparing the effect of <strong class="intervention">Apixaban</strong><br>
on the incidence of <strong class="outcome"></strong><br>
in patients with <strong class="condition"></strong>.</h1>

<div class="iframe">
    <iframe name="ifrm" id="ifrm" src="#" frameborder="0" style="display:none">
        Your browser doesn't support iframes.
    </iframe>
</div>

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
  $(".home-text[name='intervention']").delay(1200).animate({
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
  // $(".home-form").submit();

  $(".home").delay(2500).fadeOut();
  $(".main-h1").delay(2900).slideDown();
function loadIframe(url) {
    var $iframe = $('#ifrm');
    if ( $iframe.length ) {
        $iframe.attr('src','https://metatrial.shinyapps.io/Hack2/?link=' + url);   
        return false;
    }
    return true;
}

var condition = $(".home-text[name='condition']").val();
var intervention = $(".home-text[name='intervention']").val();
var outcome = $(".home-text[name='outcome']").val();
var data = {
  'condition': condition,
  'intervention': intervention,
  'outcome': outcome,
};
$(".intervention").text(intervention);
$(".condition").text(condition);
$(".outcome").text(outcome);
  $.ajax({
    url: '/search',
    method: 'POST',
    data: data, _token:'{!! csrf_token() !!}',
    success: function(data) {
      loadIframe(data);
      console.log(data);
      $("#ifrm").delay(2000).fadeIn();

    },
  });
  

});



</script>



  </body>
</html>
