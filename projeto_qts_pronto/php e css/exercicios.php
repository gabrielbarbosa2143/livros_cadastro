<?php
include('config.php');
include('protect.php');

$msg = '';

if (isset($_POST['add'])) { // Adiciona exercício
    if (!isset($_SESSION['usuario_id'])) {
        $msg = 'Erro: Usuário não está logado!';
    } else {
        $nome_ex = $_POST['nome_ex'];
        $descricao = $_POST['descricao'];
        $usuario_id = $_SESSION['usuario_id']; // Pegando o ID do usuário logado

        $sql = "INSERT INTO exercicios (nome_ex, descricao, usuario_id) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $nome_ex, $descricao, $usuario_id);
        if ($stmt->execute()) {
            $msg = 'Livro adicionado com sucesso!';
        } else {
            $msg = 'Erro ao adicionar livro!';
        }
        $stmt->close();
    }
}

if (isset($_POST['edit'])) { // Editar exercício
    if (!isset($_SESSION['usuario_id'])) {
        $msg = 'Erro: Usuário não está logado!';
    } else {
        $id = $_POST['id'];
        $nome_ex = $_POST['nome_ex'];
        $descricao = $_POST['descricao'];
        $usuario_id = $_SESSION['usuario_id']; // Pegando o ID do usuário logado

        $sql = "UPDATE exercicios SET nome_ex = ?, descricao = ? WHERE id = ? AND usuario_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssii", $nome_ex, $descricao, $id, $usuario_id);
        if ($stmt->execute()) {
            $msg = 'Livro atualizado com sucesso!';
        } else {
            $msg = 'Erro ao atualizar livro!';
        }
        $stmt->close();
    }
}

if (isset($_GET['delete'])) { // Deletar exercício
    if (!isset($_SESSION['usuario_id'])) {
        $msg = 'Erro: Usuário não está logado!';
    } else {
        $id = $_GET['delete'];
        $usuario_id = $_SESSION['usuario_id']; // Pegando o ID do usuário logado

        $sql = "DELETE FROM exercicios WHERE id = ? AND usuario_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $id, $usuario_id);
        if ($stmt->execute()) {
            $msg = 'Livro excluído com sucesso!';
        } else {
            $msg = 'Erro ao excluir livro!';
        }
        $stmt->close();
    }
}

// Selecionar os exercícios do usuário logado
if (isset($_SESSION['usuario_id'])) {
    $sql = "SELECT * FROM exercicios WHERE usuario_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_SESSION['usuario_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $exercicios = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $exercicios = [];
    $msg = 'Erro: Usuário não está logado!';
}

$edit_exercicio = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    if (isset($_SESSION['usuario_id'])) {
        $sql = "SELECT * FROM exercicios WHERE id = ? AND usuario_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $id, $_SESSION['usuario_id']);
        $stmt->execute();
        $edit_exercicio = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="sttle3.css">
    <title>Gerenciar Exercícios</title>
    <script>
        function confirmDelete(id) {
            if (confirm("Você tem certeza que deseja excluir este livro?")) {
                window.location.href = "exercicios.php?delete=" + id;
            }
        }
    </script>
</head>
<body>
    <h1>Gerenciar Livros</h1>
    
    <?php if ($msg): ?>
        <p style="color: green;"><?php echo $msg; ?></p>
    <?php endif; ?>
    
    <center>
        <form action="exercicios.php" method="POST">
            <input type="text" name="nome_ex" id="nome_ex" placeholder="Nome do Livro" required 
                   value="<?php echo $edit_exercicio ? htmlspecialchars($edit_exercicio['nome_ex']) : ''; ?>">
            <textarea name="descricao" id="descricao" placeholder="Descrição do Livro" required><?php echo $edit_exercicio ? htmlspecialchars($edit_exercicio['descricao']) : ''; ?></textarea>
            <button type="submit" name="<?php echo $edit_exercicio ? 'edit' : 'add'; ?>">
                <?php echo $edit_exercicio ? 'Atualizar Livro' : 'Adicionar Livro'; ?>
            </button>
            
            <?php if ($edit_exercicio): ?>
                <input type="hidden" name="id" value="<?php echo $edit_exercicio['id']; ?>">
            <?php endif; ?>
        </form>
    </center>

    <h2>Lista de Livros</h2>
    <table border="1">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Descrição</th>
                <th>Editar</th>
                <th>Excluir</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($exercicios as $exercicio): ?>
            <tr>
                <td><?php echo htmlspecialchars($exercicio['nome_ex']); ?></td>
                <td><?php echo htmlspecialchars($exercicio['descricao']); ?></td>
                <td>
                    <a href="exercicios.php?edit=<?php echo $exercicio['id']; ?>">Editar</a>
                </td>
                <td>
                    <a href="javascript:void(0);" onclick="confirmDelete(<?php echo $exercicio['id']; ?>)">Excluir</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <p><a href="logout.php">Sair</a></p>
</body>
</html>
