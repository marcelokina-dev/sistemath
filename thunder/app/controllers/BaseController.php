<?php

// Importante: Certifique-se que o arquivo de Config (com a constante BASE_URL) 
// seja carregado no seu index.php principal.

class BaseController
{
    /**
     * Renderiza a view injetando o header, sidebar e footer automaticamente.
     */
    protected function render($view, $data = [])
    {
        if (!empty($data)) {
            extract($data);
        }

        $path = __DIR__ . '/../views/';

        if (ob_get_length())
            ob_clean();

        // 1. Topo do Layout
        if (file_exists($path . 'layout/header.php'))
            include $path . 'layout/header.php';
            
        if (file_exists($path . 'layout/sidebar.php'))
            include $path . 'layout/sidebar.php';

        // 2. Conteúdo da Página
        $viewFile = $path . "{$view}.php";
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            echo "<div class='container mt-5'><div class='alert alert-danger'>Erro: View <strong>{$view}</strong> não encontrada.</div></div>";
        }

        // 3. Rodapé do Layout
        if (file_exists($path . 'layout/footer.php'))
            include $path . 'layout/footer.php';
    }

    /**
     * NOVO: Gera uma URL absoluta baseada na BASE_URL configurada.
     * Use nas views: <a href="<?= $this->url('/eventos/lutas') ?>">
     */
    protected function url($path = '')
    {
        // Remove barras duplicadas para evitar caminhos como //eventos
        $path = ltrim($path, '/');
        return BASE_URL . '/' . $path;
    }

    /**
     * NOVO: Redireciona para um caminho interno de forma segura.
     * Use no Controller: return $this->redirect('/eventos?msg=sucesso');
     */
    protected function redirect($path)
    {
        header("Location: " . $this->url($path));
        exit;
    }
}