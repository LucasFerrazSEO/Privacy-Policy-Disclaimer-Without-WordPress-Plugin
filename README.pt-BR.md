[English](README.md) · **Português (Brasil)**

# Privacy-Policy-Disclaimer-Without-WordPress-Plugin

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

Aviso de cookies (banner "ao usar este site, você aceita...") em HTML e
JavaScript puro, para colar no rodapé do tema sem instalar plugin de
consentimento de cookies. Não tem dependência nenhuma.

## Sumário

- [Recursos](#recursos)
- [Uso](#uso)
- [Perguntas frequentes](#perguntas-frequentes)
- [Limitações](#limitações)
- [Como contribuir](#como-contribuir)
- [Autor](#autor)
- [Licença](#licença)

## Recursos

Um banner fixo que aparece na primeira visita, com um link para a
página de política de privacidade e um botão "OK". Ao clicar em OK, o
banner some (sem recarregar a página) e grava um cookie por 30 anos
(10950 dias) para não aparecer de novo no mesmo navegador.

O código foi revisado nesta versão. O botão "OK" agora é um
`<button>` de verdade (antes era uma `<div>` com `onclick`, que não
recebe foco por teclado nem é lido corretamente por leitor de tela), e
duas funções mortas que nunca eram chamadas (uma que reexibia o banner
sem necessidade e outra que atualizava um relógio que não existe neste
snippet) foram removidas.

## Uso

1. Copie o conteúdo de `Code.txt`.
2. Cole no `footer.php` do seu tema (ou em um bloco de HTML
   personalizado, se o seu tema ou construtor de site permitir).
3. Confirme a URL da política de privacidade. O código usa
   `bloginfo('url')` mais `/politica-de-privacidade/`. Se a sua página
   de política tiver outra URL, ajuste a variável `privacy_policy` na
   linha 3 do arquivo.
4. Publique e visite o site em uma aba anônima para conferir se o
   banner aparece e some corretamente ao clicar em OK.

## Perguntas frequentes

**Isso deixa o site em conformidade com a LGPD?**
Não sozinho. Esse é um aviso simples de cookies, não uma ferramenta
completa de gestão de consentimento (que exigiria, por exemplo,
bloquear scripts de terceiros até o usuário aceitar, granularidade por
categoria de cookie e registro do consentimento). Para conformidade
completa com a LGPD, avalie uma ferramenta dedicada de consentimento
ou orientação jurídica específica.

**Funciona fora do WordPress?**
A única parte específica do WordPress é `<?php bloginfo('url'); ?>` na
linha 3, que gera a URL do site. Fora do WordPress, basta trocar esse
trecho pela URL fixa do seu site.

**Precisa de plugin ou biblioteca externa?**
Não. É HTML e JavaScript puro, sem dependência.

**O banner recarrega a página ao aceitar?**
Não. Clicar em OK só grava o cookie e esconde o banner. A página
continua exatamente como estava.

## Limitações

Aviso de cookies simples (notice banner), não uma ferramenta de
consentimento granular. Não bloqueia scripts de terceiros antes do
aceite, não registra data e hora do consentimento e não distingue
categorias de cookie (necessário, analytics, marketing).

## Como contribuir

Relatos de erro e sugestões são bem-vindos pelas [Issues do GitHub](https://github.com/LucasFerrazSEO/Privacy-Policy-Disclaimer-Without-WordPress-Plugin/issues).

## Autor

[Lucas Ferraz](https://lucasferraz.com) é especialista em SEO, criação de sites e Generative Engine Optimization e fundador da [Lucas Ferraz SEO](https://lucasferrazseo.com).

## Licença

MIT. Veja o arquivo [LICENSE](LICENSE).
