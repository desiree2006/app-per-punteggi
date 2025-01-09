<?php
// Connessione al database SQLite
$db = new SQLite3('calcolatrice.db');

// Crea la tabella, se non esiste
$db->exec("CREATE TABLE IF NOT EXISTS calcolatrice (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    valore1 REAL NOT NULL,
    valore2 REAL NOT NULL,
    operazione TEXT NOT NULL,
    risultato REAL NOT NULL,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// Variabili iniziali
$valore1 = '';
$valore2 = '';
$operazione = '';
$risultato = '';
$messaggioErrore = '';

// Gestione input dell'utente
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Leggi i valori inviati
    $valore1 = $_POST['valore1'] ?? '';
    $valore2 = $_POST['valore2'] ?? '';
    $operazione = $_POST['operazione'] ?? '';

    // Verifica che i valori siano numeri
    if (is_numeric($valore1) && is_numeric($valore2)) {
        switch ($operazione) {
            case 'somma':
                $risultato = $valore1 + $valore2;
                break;
            case 'sottrazione':
                $risultato = $valore1 - $valore2;
                break;
            case 'moltiplicazione':
                $risultato = $valore1 * $valore2;
                break;
            case 'divisione':
                if ($valore2 != 0) {
                    $risultato = $valore1 / $valore2;
                } else {
                    $messaggioErrore = 'Errore: Divisione per zero!';
                }
                break;
            default:
                $messaggioErrore = 'Operazione non valida!';
        }

        // Salva l'operazione nel database
        if (empty($messaggioErrore)) {
            $db->exec("INSERT INTO calcolatrice (valore1, valore2, operazione, risultato) 
                       VALUES ($valore1, $valore2, '$operazione', $risultato)");
        }
    } else {
        $messaggioErrore = 'Inserisci solo numeri validi!';
    }
}

// Recupera la cronologia delle operazioni
$history = $db->query("SELECT * FROM calcolatrice ORDER BY id DESC LIMIT 10");
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calcolatrice</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="calculator">
        <h1>Calcolatrice</h1>
        <form method="POST">
            <input type="number" name="valore1" placeholder="Valore 1" step="any" required>
            <select name="operazione" required>
                <option value="somma">+</option>
                <option value="sottrazione">-</option>
                <option value="moltiplicazione">×</option>
                <option value="divisione">÷</option>
            </select>
            <input type="number" name="valore2" placeholder="Valore 2" step="any" required>
            <button type="submit">Calcola</button>
        </form>

        <!-- Mostra risultato -->
        <?php if (!empty($risultato)): ?>
            <p>Risultato: <?php echo htmlspecialchars($risultato); ?></p>
        <?php endif; ?>

        <!-- Mostra messaggi di errore -->
        <?php if (!empty($messaggioErrore)): ?>
            <p style="color: red;"><?php echo htmlspecialchars($messaggioErrore); ?></p>
        <?php endif; ?>

        <h3>Storico Operazioni</h3>
        <ul>
            <?php while ($row = $history->fetchArray()): ?>
                <li>
                    <?php echo $row['valore1'] . ' ' . $row['operazione'] . ' ' . $row['valore2']; ?> 
                    = <?php echo $row['risultato']; ?> 
                    (<?php echo $row['timestamp']; ?>)
                </li>
            <?php endwhile; ?>
        </ul>
    </div>
</body>
</html>
