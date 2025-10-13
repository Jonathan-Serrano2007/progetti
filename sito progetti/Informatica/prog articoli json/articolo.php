<?php
class Articolo {
    private $titolo;
    private $descrizione;
    private $prezzo;
    private $immagine;

    public function __construct($titolo, $descrizione, $prezzo, $immagine) {
        $this->titolo = $titolo;
        $this->descrizione = $descrizione;
        $this->prezzo = $prezzo;
        $this->immagine = $immagine;
    }

    public function show() {
            echo '
            <div class="card" style="width: 18rem;">
                <img src="immagini/' . htmlspecialchars($this->immagine) . '" class="card-img-top" alt="immagine articolo">
                <div class="card-body">
                    <h5 class="card-title">' . htmlspecialchars($this->titolo) . '</h5>
                    <p class="card-text">' . htmlspecialchars($this->descrizione) . '</p>
                    <p class="card-text"><strong>Prezzo: € ' . number_format($this->prezzo, 2, ',', '.') . '</strong></p>
                </div>
            </div>';
    }
}
?>
