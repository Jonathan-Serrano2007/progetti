<?php include 'includes/header.php'; ?>

<?php
$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://api.thecatapi.com/v1/images/search?limit=6',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_FOLLOWLOCATION => true
));
$response = curl_exec($curl);
curl_close($curl);

$data = json_decode($response, true);
?>

<section class="contenuto">
  <h2>Galleria dei nostri amici felini</h2>
  <div class="galleria">
    <?php foreach ($data as $cat): ?>
      <div class="card">
        <img src="<?php echo $cat['url']; ?>" alt="Gatto">
      </div>
    <?php endforeach; ?>
  </div>
  <button onclick="window.location.reload()">Ricarica galleria 🐈</button>
</section>

<?php include 'includes/footer.php'; ?>
