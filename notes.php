<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

$notesFile = 'notes.json';

// Criar arquivo se não existir
if (!file_exists($notesFile)) {
    file_put_contents($notesFile, json_encode([]));
}

// Função para ler notas
function readNotes() {
    global $notesFile;
    $content = file_get_contents($notesFile);
    return json_decode($content, true) ?: [];
}

// Função para salvar notas
function saveNotes($notes) {
    global $notesFile;
    file_put_contents($notesFile, json_encode($notes, JSON_PRETTY_PRINT));
}

// Processar requisições
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Retornar todas as notas
        echo json_encode(readNotes());
        break;

    case 'POST':
        // Adicionar nova nota
        $data = json_decode(file_get_contents('php://input'), true);
        if (isset($data['title']) && isset($data['content'])) {
            $notes = readNotes();
            $note = [
                'id' => time(),
                'title' => $data['title'],
                'content' => $data['content'],
                'date' => date('d/m/Y')
            ];
            $notes[] = $note;
            saveNotes($notes);
            echo json_encode(['success' => true, 'note' => $note]);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Dados inválidos']);
        }
        break;

    case 'DELETE':
        // Deletar nota
        $data = json_decode(file_get_contents('php://input'), true);
        if (isset($data['id'])) {
            $notes = readNotes();
            $notes = array_filter($notes, function($note) use ($data) {
                return $note['id'] != $data['id'];
            });
            saveNotes(array_values($notes));
            echo json_encode(['success' => true]);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'ID não fornecido']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Método não permitido']);
        break;
}
?> 
 