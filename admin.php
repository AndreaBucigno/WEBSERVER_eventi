<?php
// admin.php
include_once 'db.php';

$message = "";
$alert_class = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_event'])) {
        try {
            $stmt = $conn->prepare("INSERT INTO events (title, description, event_date, available_seats) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$_POST['title'], $_POST['description'], $_POST['event_date'], $_POST['seats']])) {
                $message = "Evento aggiunto con successo!";
                $alert_class = "alert-success";
            }
        } catch (Exception $e) {
            $message = "Errore durante l'inserimento: " . $e->getMessage();
            $alert_class = "alert-danger";
        }
    } elseif (isset($_POST['delete_id'])) {
        try {
            $stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
            if ($stmt->execute([$_POST['delete_id']])) {
                $message = "Evento eliminato correttamente.";
                $alert_class = "alert-warning";
            }
        } catch (Exception $e) {
            $message = "Errore durante l'eliminazione.";
            $alert_class = "alert-danger";
        }
    }
}

// Recupero eventi per la visualizzazione
$events = $conn->query("SELECT * FROM events ORDER BY event_date ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione CityEvents</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="bi bi-calendar-event me-2"></i>PAGINA ADMIN PER LA GESTIONE DEGLI EVENTI - BUCIGNO ANDREA 5dinf
            </a>
        </div>
    </nav>

    <div class="container">
        
        <?php if($message): ?>
            <div class="alert <?= $alert_class ?> alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-info-circle-fill me-2"></i><?= $message ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                        <h5 class="text-primary"><i class="bi bi-plus-square me-2"></i>Nuovo Evento</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-secondary">Titolo dell'evento</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-type"></i></span>
                                    <input type="text" class="form-control" name="title" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold text-secondary">Descrizione</label>
                                <textarea class="form-control" name="description" rows="3"></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold text-secondary">Data</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-calendar-date"></i></span>
                                    <input type="date" class="form-control" name="event_date" required>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold text-secondary">Posti disponibili</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-people"></i></span>
                                    <input type="number" class="form-control" name="seats" min="1" required>
                                </div>
                            </div>

                            <button type="submit" name="add_event" class="btn btn-primary w-100">
                                <i class="bi bi-save me-2"></i>Salva Evento
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card h-100">
                    <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                        <h5 class="text-primary"><i class="bi bi-list-ul me-2"></i>Eventi in programma</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-3">ID</th>
                                        <th>Titolo</th>
                                        <th>Data</th>
                                        <th class="text-center">Posti</th>
                                        <th class="text-end pe-3">Azione</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($events)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">
                                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                                Nessun evento presente.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($events as $e): ?>
                                        <tr>
                                            <td class="ps-3 text-muted">#<?= $e['id'] ?></td>
                                            <td class="fw-bold"><?= htmlspecialchars($e['title']) ?></td>
                                            <td><?= date('d/m/Y', strtotime($e['event_date'])) ?></td>
                                            <td class="text-center">
                                                <span class="badge bg-<?= $e['available_seats'] > 10 ? 'success' : 'danger' ?> rounded-pill px-3 py-2">
                                                    <?= $e['available_seats'] ?>
                                                </span>
                                            </td>
                                            <td class="text-end pe-3">
                                                <form method="POST" style="display:inline;" onsubmit="return confirm('Sei sicuro di voler eliminare questo evento?');">
                                                    <input type="hidden" name="delete_id" value="<?= $e['id'] ?>">
                                                    <button type="submit" class="btn btn-outline-danger btn-sm">
                                                        <i class="bi bi-trash3"></i> Elimina
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>