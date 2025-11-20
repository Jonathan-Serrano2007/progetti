<!doctype html>
<html lang="it">
	<head>
		<!-- Required meta tags -->
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
		<title>Serranò Jonathan Progetti Scolastici 25/26 - GPO</title>
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous" />
		<style>
			.card-hover{ transition: transform .15s, box-shadow .15s; }
			.card-hover:hover{ transform: translateY(-6px); box-shadow: 0 8px 20px rgba(0,0,0,.12); }
		</style>
	</head>
	<body>
		<header>
			<!-- semplice navbar -->
			<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
				<div class="container">
					<a class="navbar-brand" href="GPO.php">GPO</a>
				</div>
			</nav>
		</header>

		<main class="container py-4">
			<div class="row justify-content-center mb-3">
				<h2 class="text-center">GPO - Elenco esercizi</h2>
			</div>

			<div class="row g-3">
				<!-- Diagramma dei casi d'uso -> img/casi_uso.png -->
				<div class="col-12 col-md-4">
					<div class="card card-hover h-100">
						<div class="card-body d-flex flex-column">
							<h5 class="card-title">Diagramma dei casi d'uso</h5>
							<p class="card-text">Visualizza il diagramma dei casi d'uso.</p>
							<div class="mt-auto">
								<a href="view_gpo.php?img=casi_uso" class="btn btn-primary">Apri</a>
							</div>
						</div>
					</div>
				</div>

				<!-- Diagramma delle classi -> img/classi.png -->
				<div class="col-12 col-md-4">
					<div class="card card-hover h-100">
						<div class="card-body d-flex flex-column">
							<h5 class="card-title">Diagramma delle classi</h5>
							<p class="card-text">Visualizza il diagramma delle classi.</p>
							<div class="mt-auto">
								<a href="view_gpo.php?img=classi" class="btn btn-primary">Apri</a>
							</div>
						</div>
					</div>
				</div>

				<!-- Diagramma E/R -> img/ER.png -->
				<div class="col-12 col-md-4">
					<div class="card card-hover h-100">
						<div class="card-body d-flex flex-column">
							<h5 class="card-title">Diagramma E/R</h5>
							<p class="card-text">Visualizza il diagramma E/R.</p>
							<div class="mt-auto">
								<a href="view_gpo.php?img=ER" class="btn btn-primary">Apri</a>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="row mt-4">
				<div class="col text-center">
					<a href="../index.php" class="btn btn-outline-secondary">Torna alla home</a>
				</div>
			</div>
		</main>

		<footer class="py-3 bg-light text-center">
			<div class="container">
				<small>© Serranò Jonathan - Progetti Scolastici</small>
			</div>
		</footer>

		<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" crossorigin="anonymous"></script>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
	</body>
</html>
