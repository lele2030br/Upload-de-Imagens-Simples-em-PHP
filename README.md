# Upload de Imagens Simples em PHP

Este projeto é um site simples para upload anônimo de imagens, desenvolvido em PHP puro, **sem necessidade de cadastro, login ou banco de dados**. Todas as imagens são armazenadas no sistema de arquivos do servidor, dentro de uma pasta protegida.

## Funcionalidades

- **Upload de Imagens:** Envie imagens JPG, PNG ou GIF de até 5MB.
- **Barra de Progresso:** Acompanhe o progresso do upload em tempo real.
- **Galeria Moderna:** Visualize as imagens já enviadas, exibidas em um grid responsivo e estilizado, com opções para abrir ou baixar cada imagem.
- **Interface Responsiva:** Layout moderno e adaptável para uso em dispositivos móveis ou desktop.
- **Segurança:** 
  - Somente imagens são aceitas (validação real de MIME type).
  - Geração de nome único para cada arquivo.
  - Pasta de uploads protegida contra execução de scripts.
- **Anônimo:** Não é necessário cadastro ou autenticação.

## Como usar

1. **Clone ou envie os arquivos para seu servidor PHP:**

   ```
   git clone <repo-url>
   ```
   Ou faça upload dos arquivos para o seu serviço de hospedagem.

2. **Permissões:**
   - O script cria automaticamente a pasta `uploads/` se ela não existir.
   - Certifique-se de que o usuário do servidor web tem permissão de escrita no diretório onde o site está hospedado.

3. **Acesse o site no navegador:**
   - Abra `index.php` em seu navegador.
   - Faça upload de imagens e veja a galeria em funcionamento.

## Estrutura dos arquivos

```
/
├── index.php         # Página principal e lógica completa do site
├── uploads/          # Pasta onde as imagens enviadas ficam armazenadas
└── README.md         # Este arquivo
```

## Considerações de Segurança

- A pasta `uploads/` contém um arquivo `.htaccess` que desabilita a execução de scripts, protegendo contra uploads maliciosos.
- Apenas arquivos de imagem com tipos MIME válidos são aceitos.
- O nome dos arquivos é sempre gerado automaticamente e de forma segura.

## Personalização

Sinta-se à vontade para modificar o CSS ou a estrutura da galeria conforme desejar. O código está bem comentado para facilitar personalizações.

## Exemplo de Uso

![Exemplo de interface do site](docs/demo.png)

## Licença

Este projeto está sob a licença MIT. Sinta-se livre para usar, modificar e distribuir!

---

Desenvolvido com ❤️ em PHP.