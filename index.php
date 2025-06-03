<?php
// Configurações
$uploadDir = __DIR__ . '/uploads/';
$uploadUrl = 'uploads/';
$maxFileSize = 5 * 1024 * 1024; // 5MB
$allowedMimeTypes = [
    'image/jpeg',
    'image/png',
    'image/gif',
];

// Mensagens de feedback
$feedback = '';
$imageUrl = null;

// Garante que o diretório de uploads exista e tenha permissão adequada
if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        die('Erro: Não foi possível criar o diretório de uploads.');
    }
}

// Desabilita execução de scripts na pasta uploads via .htaccess
$htaccessPath = $uploadDir . '.htaccess';
if (!file_exists($htaccessPath)) {
    file_put_contents($htaccessPath, "php_flag engine off\n<FilesMatch \"\\.(php|phtml|php3|php4|php5|php7|phps|cgi|pl|py|jsp|asp|aspx|sh|bat)\$\">\n    Order Allow,Deny\n    Deny from all\n</FilesMatch>\n");
}

// Processamento do upload AJAX (retorna JSON)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['ajax']) && $_GET['ajax'] == '1' && isset($_FILES['imagem'])) {
    $file = $_FILES['imagem'];

    $response = ['success' => false, 'message' => '', 'imageUrl' => null];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $response['message'] = 'Erro ao enviar o arquivo.';
    } elseif ($file['size'] > $maxFileSize) {
        $response['message'] = 'Arquivo muito grande. O tamanho máximo permitido é 5MB.';
    } else {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedMimeTypes)) {
            $response['message'] = 'Tipo de arquivo não permitido. Apenas JPG, PNG e GIF são aceitos.';
        } else {
            $ext = '';
            switch ($mimeType) {
                case 'image/jpeg': $ext = '.jpg'; break;
                case 'image/png':  $ext = '.png'; break;
                case 'image/gif':  $ext = '.gif'; break;
            }
            $uniqueName = uniqid('img_', true) . $ext;
            $destPath = $uploadDir . $uniqueName;

            if (move_uploaded_file($file['tmp_name'], $destPath)) {
                $response['success'] = true;
                $response['message'] = 'Upload realizado com sucesso!';
                $response['imageUrl'] = $uploadUrl . $uniqueName;
            } else {
                $response['message'] = 'Erro ao salvar o arquivo no servidor.';
            }
        }
    }
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Função para listar imagens na pasta de uploads
function listarImagens($diretorio, $urlBase) {
    $imagens = [];
    if (!is_dir($diretorio)) return $imagens;
    $files = scandir($diretorio);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..' || $file === '.htaccess') continue;
        $path = $diretorio . $file;
        if (is_file($path)) {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                $imagens[filemtime($path) . '-' . $file] = $urlBase . $file;
            }
        }
    }
    // Ordena por data de modificação (mais recentes primeiro)
    krsort($imagens);
    return $imagens;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Upload de Imagens Simples</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Google Fonts for a modern look -->
    <link href="https://fonts.googleapis.com/css?family=Montserrat:700,400&display=swap" rel="stylesheet">
    <!-- Icon for the upload button -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root {
            --primary: #4f8cff;
            --primary-dark: #3367d6;
            --background: #f5f8fa;
            --container-bg: #fff;
            --border-radius: 18px;
            --shadow: 0 4px 24px rgba(80, 104, 145, 0.07);
            --accent: #eafbff;
            --success: #29be46;
            --danger: #e14b4b;
            --gallery-bg: #f1f5fa;
            --gallery-card-bg: #fff;
        }
        body {
            font-family: 'Montserrat', Arial, sans-serif;
            margin: 0;
            background: var(--background);
            min-height: 100vh;
        }
        .container {
            max-width: 600px;
            margin: 36px auto 0 auto;
            background: var(--container-bg);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            padding: 36px 32px 32px 32px;
            position: relative;
            overflow: hidden;
        }
        h1, h2 {
            text-align: center;
            font-weight: 700;
            color: var(--primary-dark);
            margin-top: 0;
        }
        h2 {
            margin-bottom: 12px;
            font-size: 1.2rem;
            letter-spacing: 1px;
        }
        form#uploadForm {
            display: flex;
            flex-direction: column;
            gap: 18px;
            background: var(--accent);
            border-radius: 10px;
            padding: 20px 16px 12px 16px;
            box-shadow: 0 2px 6px rgba(80,104,145,0.04);
        }
        label {
            font-weight: 500;
            color: #333;
        }
        input[type="file"] {
            background: #fff;
            border: 1px solid #d6eaff;
            border-radius: 6px;
            padding: 10px;
            width: 100%;
            font-size: 1em;
            color: #333;
            transition: border-color .2s;
        }
        input[type="file"]:focus {
            border-color: var(--primary);
            outline: none;
        }
        button[type="submit"] {
            background: var(--primary);
            color: #fff;
            font-size: 1.08em;
            font-weight: 700;
            border: none;
            border-radius: 6px;
            padding: 12px 0;
            cursor: pointer;
            transition: background .18s;
            margin-top: 6px;
            box-shadow: 0 2px 8px rgba(80,104,145,0.07);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
        }
        button[type="submit"]:hover, button[type="submit"]:focus {
            background: var(--primary-dark);
        }
        .progress-bar-bg {
            width: 100%;
            height: 18px;
            background: #e3eafc;
            border-radius: 10px;
            overflow: hidden;
            margin-top: 6px;
            display: none;
        }
        .progress-bar-fill {
            height: 100%;
            width: 0;
            background: linear-gradient(90deg, #4f8cff 60%, #8fd3fe 100%);
            transition: width .3s;
        }
        .feedback {
            margin: 20px 0 0 0;
            padding: 13px 15px;
            border-radius: 7px;
            font-weight: 500;
            box-shadow: 0 2px 8px rgba(80,104,145,0.05);
            text-align: center;
            font-size: 1.08em;
        }
        .sucesso { background: #eafbe8; color: var(--success); border: 1.5px solid #b5efc2; }
        .erro    { background: #ffeaea; color: var(--danger); border: 1.5px solid #ffd6d6; }
        .imagem-upload {
            text-align: center;
            margin: 18px 0 5px 0;
            animation: fadeIn .8s;
        }
        .imagem-upload img {
            max-width: 220px;
            max-height: 220px;
            margin: 0 auto 7px auto;
            border-radius: 10px;
            box-shadow: 0 2px 12px #8fd3fe44;
            border: 2px solid #ddefff;
            background: #fff;
        }
        .imagem-upload p, .imagem-upload a {
            font-size: 1em;
            color: var(--primary-dark);
            text-decoration: none;
        }
        .imagem-upload a:hover { text-decoration: underline; }
        .galeria {
            margin-top: 32px;
            padding-top: 6px;
            background: var(--gallery-bg);
            border-radius: 20px;
            box-shadow: 0 2px 10px #e3eafc;
            padding-bottom: 20px;
        }
        .galeria h2 {
            margin-top: 0;
            padding-top: 14px;
        }
        .imagens {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 22px;
            justify-content: center;
            margin: 22px 10px 0 10px;
        }
        .imagens-card {
            background: var(--gallery-card-bg);
            border-radius: 16px;
            box-shadow: 0 2px 12px #e3eafc;
            overflow: hidden;
            transition: transform .17s, box-shadow .17s;
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 12px 5px 12px 5px;
            border: 2px solid transparent;
        }
        .imagens-card:hover, .imagens-card:focus-within {
            transform: translateY(-6px) scale(1.04);
            box-shadow: 0 6px 20px #8fd3fe4d;
            border-color: #4f8cff25;
        }
        .imagens-card img {
            max-width: 120px;
            max-height: 120px;
            border-radius: 10px;
            transition: filter .18s;
            box-shadow: 0 2px 10px #dceaff;
        }
        .imagens-card .card-actions {
            margin-top: 9px;
            display: flex;
            gap: 10px;
            justify-content: center;
            width: 100%;
        }
        .imagens-card .card-actions a {
            background: var(--primary);
            color: #fff;
            padding: 6px 10px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.93em;
            font-weight: 500;
            box-shadow: 0 1px 4px #e3eafc;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: background .14s;
        }
        .imagens-card .card-actions a:hover, .imagens-card .card-actions a:focus {
            background: var(--primary-dark);
        }
        .imagens-card .card-date {
            margin-top: 6px;
            font-size: 0.85em;
            color: #8ca0c6;
            text-align: center;
        }
        @media (max-width: 650px) {
            .container { max-width: 99vw; padding: 10px; }
            form#uploadForm { padding: 11px 6px 10px 6px; }
            .imagem-upload img { max-width: 120px; max-height: 120px; }
            .imagens-card img { max-width: 70px; max-height: 70px; }
            .imagens { gap: 13px; }
        }
        @media (max-width: 400px) {
            .container { padding: 3vw 1vw 1vw 1vw; }
            .imagem-upload img { max-width: 80px; max-height: 80px; }
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(35px);}
            to   { opacity: 1; transform: none;}
        }
        /* Scrollbar styling */
        ::-webkit-scrollbar { width: 8px; background: var(--accent);}
        ::-webkit-scrollbar-thumb { background: #cbe1ff; border-radius: 8px;}
        /* Responsive feedback */
        #feedbackArea:empty { display: none; }
        #imgArea:empty { display: none; }
    </style>
</head>
<body>
<div class="container">
    <h1><i class="fa-solid fa-image"></i> Upload de Imagens</h1>
    <form id="uploadForm" method="POST" enctype="multipart/form-data" autocomplete="off">
        <label for="imagem">Selecione uma imagem (JPG, PNG, GIF) <span style="font-weight:400;">até 5MB</span>:</label>
        <input type="file" name="imagem" id="imagem" accept="image/jpeg, image/png, image/gif" required>
        <button type="submit" id="enviarBtn"><i class="fa-solid fa-cloud-arrow-up"></i> Enviar Imagem</button>
        <div class="progress-bar-bg" id="progressBg">
            <div class="progress-bar-fill" id="progressFill"></div>
        </div>
    </form>

    <div id="feedbackArea"></div>
    <div id="imgArea"></div>

    <div class="galeria" id="galeria">
        <h2><i class="fa-regular fa-images"></i> Galeria de Imagens Enviadas</h2>
        <div class="imagens" id="imagensDiv">
            <?php
            $imagens = listarImagens($uploadDir, $uploadUrl);
            if (empty($imagens)) {
                echo "<p style='width:100%;text-align:center;color:#b2b9c6;'>Nenhuma imagem enviada ainda.</p>";
            } else {
                foreach ($imagens as $key => $imgUrl) {
                    // Data formatada
                    $filename = basename($imgUrl);
                    $filepath = $uploadDir . $filename;
                    $filedate = file_exists($filepath) ? date('d/m/Y H:i', filemtime($filepath)) : '';
                    echo '<div class="imagens-card" tabindex="0">';
                    echo '  <a href="' . htmlspecialchars($imgUrl) . '" target="_blank" title="Ver imagem em tamanho real">';
                    echo '      <img src="' . htmlspecialchars($imgUrl) . '" alt="Imagem">';
                    echo '  </a>';
                    echo '  <div class="card-actions">';
                    echo '      <a href="' . htmlspecialchars($imgUrl) . '" download title="Baixar imagem"><i class="fa-solid fa-download"></i> Baixar</a>';
                    echo '      <a href="' . htmlspecialchars($imgUrl) . '" target="_blank" title="Abrir em nova guia"><i class="fa-solid fa-up-right-from-square"></i> Abrir</a>';
                    echo '  </div>';
                    echo '  <div class="card-date"><i class="fa-regular fa-clock"></i> ' . $filedate . '</div>';
                    echo '</div>';
                }
            }
            ?>
        </div>
    </div>
</div>
<script>
document.getElementById('uploadForm').addEventListener('submit', function(e){
    e.preventDefault();

    let fileInput = document.getElementById('imagem');
    let file = fileInput.files[0];
    if (!file) return;

    let progressBg = document.getElementById('progressBg');
    let progressFill = document.getElementById('progressFill');
    let feedbackArea = document.getElementById('feedbackArea');
    let imgArea = document.getElementById('imgArea');
    let enviarBtn = document.getElementById('enviarBtn');

    // Limpa mensagens
    feedbackArea.innerHTML = '';
    imgArea.innerHTML = '';

    // Mostra a barra de progresso
    progressBg.style.display = 'block';
    progressFill.style.width = '0';

    enviarBtn.disabled = true;

    let formData = new FormData();
    formData.append('imagem', file);

    let xhr = new XMLHttpRequest();
    xhr.open('POST', '?ajax=1', true);

    xhr.upload.onprogress = function(e){
        if (e.lengthComputable) {
            let percent = Math.round((e.loaded / e.total) * 100);
            progressFill.style.width = percent + '%';
        }
    };

    xhr.onload = function() {
        progressBg.style.display = 'none';
        enviarBtn.disabled = false;
        if (xhr.status === 200) {
            try {
                let resp = JSON.parse(xhr.responseText);
                let msgClass = resp.success ? 'sucesso' : 'erro';
                feedbackArea.innerHTML = '<div class="feedback '+msgClass+'">'+resp.message+'</div>';
                if (resp.success && resp.imageUrl) {
                    imgArea.innerHTML = `
                        <div class="imagem-upload">
                            <p>Imagem enviada:</p>
                            <a href="${resp.imageUrl}" target="_blank">
                                <img src="${resp.imageUrl}" alt="Imagem enviada">
                            </a>
                            <p><a href="${resp.imageUrl}" target="_blank">Link direto</a></p>
                        </div>
                    `;
                    // Adiciona a imagem no topo da galeria com card moderno
                    let imagensDiv = document.getElementById('imagensDiv');
                    let now = new Date();
                    let date = ('0' + now.getDate()).slice(-2) + '/' +
                               ('0' + (now.getMonth()+1)).slice(-2) + '/' +
                               now.getFullYear() + ' ' +
                               ('0' + now.getHours()).slice(-2) + ':' +
                               ('0' + now.getMinutes()).slice(-2);
                    let card = document.createElement('div');
                    card.className = 'imagens-card';
                    card.tabIndex = 0;
                    card.innerHTML = `
                        <a href="${resp.imageUrl}" target="_blank" title="Ver imagem em tamanho real">
                            <img src="${resp.imageUrl}" alt="Imagem">
                        </a>
                        <div class="card-actions">
                            <a href="${resp.imageUrl}" download title="Baixar imagem"><i class="fa-solid fa-download"></i> Baixar</a>
                            <a href="${resp.imageUrl}" target="_blank" title="Abrir em nova guia"><i class="fa-solid fa-up-right-from-square"></i> Abrir</a>
                        </div>
                        <div class="card-date"><i class="fa-regular fa-clock"></i> ${date}</div>
                    `;
                    imagensDiv.insertBefore(card, imagensDiv.firstChild);
                }
            } catch(e) {
                feedbackArea.innerHTML = '<div class="feedback erro">Erro ao processar resposta do servidor.</div>';
            }
        } else {
            feedbackArea.innerHTML = '<div class="feedback erro">Erro na conexão com o servidor.</div>';
        }
    };

    xhr.onerror = function() {
        progressBg.style.display = 'none';
        enviarBtn.disabled = false;
        feedbackArea.innerHTML = '<div class="feedback erro">Erro na conexão com o servidor.</div>';
    };

    xhr.send(formData);
});
</script>
</body>
</html>