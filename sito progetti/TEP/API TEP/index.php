<?php include 'includes/header.php'; ?>

<?php
$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://api.thecatapi.com/v1/images/search',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_FOLLOWLOCATION => true
));
$response = curl_exec($curl);
curl_close($curl);

$data = json_decode($response, true);
$catImage = $data[0]['url'];
?>

<section class="contenuto">
  <h2>Un gatto casuale per te!</h2>
  <img src="<?php echo $catImage; ?>" alt="Gatto casuale">
  <form method="post">
    <button onclick="window.location.reload()">Mostra un altro gatto 😺</button>
  </form>
  <div style="margin-top:12px;">
    <a href="../TEP.php" class="btn btn-secondary">Torna indietro</a>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
