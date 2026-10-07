# Course Carousel 1.12.3

Plugin único `local_coursecarousel` para Moodle 4.1 ou superior: carrossel, catálogo,
vídeos, cadastro UFMG, campos de perfil e personalização do login MinhaUFMG.
Não depende do antigo `local_ufmgcadastro`.

## Instalação nova

Copie somente esta pasta para `local/coursecarousel` e execute a instalação pela
administração do Moodle. Neste workspace, esta pasta é `coursecarousel/coursecarousel`.
A instalação cria os metadados/categorias de cursos já previstos pelo carrossel
e os oito campos do perfil: CPF, nome social, nascimento, sexo, raça/cor, estado,
cidade e escolaridade. Nome social é opcional; os demais são obrigatórios.
Os campos novos têm visibilidade privada (titular e usuários com permissão para
ver os detalhes).

## Atualização de instalações existentes

1. Faça a atualização em uma janela de manutenção, com backup do banco e dos arquivos.
2. Publique esta pasta em `local/coursecarousel` e retire o diretório
   `local/ufmgcadastro` do código ativo do Moodle. Guarde a cópia antiga fora de
   `local/` caso precise reverter. Não desinstale o registro antigo no banco
   antes da migração: suas configurações serão lidas durante a atualização.
3. Execute a atualização administrativa do Moodle. O novo passo `2026091801`
   importa a URL do provedor, a preferência de personalização, os IDs dos campos
   gerenciados. As antigas opções de redirecionamento são removidas. Configurações já definidas no
   coursecarousel têm prioridade. O código antigo não é necessário para migrar.
4. Confira os campos e as configurações. Depois de validar, remova o registro
   obsoleto de `local_ufmgcadastro` na administração de plugins do Moodle.
   As versões aqui analisadas não têm rotina própria de desinstalação nem
   tabelas próprias; os campos e valores pertencem ao subsistema de perfil.
5. Verifique login local, MinhaUFMG, cadastro, retorno ao curso e matrícula
   antes de liberar o acesso.

Não mantenha os dois diretórios ativos. A migração desativa a personalização da
versão antiga 1.4 como proteção transitória; versões anteriores não respeitam
essa opção e precisam ser retiradas do código ativo na mesma implantação.
A versão 1.11.1 restaura a navegação da 1.9.1: cartões de cursos já matriculados
apontam para `/course/view.php?id=ID`; os demais, para `/enrol/index.php?id=ID`.
A autenticação e as exigências de perfil são aplicadas pelas páginas nativas.
Não há página intermediária nem opção para alterar esse fluxo.

Ao atualizar a partir da 1.11.0, retire os arquivos antigos `course.php`,
`login.css`, `classes/local/course_access.php` e `tests/course_access_test.php`
do diretório do plugin (eles não estão no pacote 1.11.1). O passo `2026091802`
remove apenas as configurações obsoletas, sem refazer a migração dos campos.
Atualize eventuais links externos para os destinos nativos acima.

## Configuração

Administração do site > Plugins > Plugins locais > Course Carousel,
seção “Cadastro e acesso UFMG”:

- **Personalizar acesso MinhaUFMG:** altera somente o link institucional existente,
  sem esconder outros provedores ou alterar seu destino.
- **URL do provedor MinhaUFMG:** URL exata exibida no login, incluindo parâmetros.
  Em branco, identifica `/auth/shibboleth/index.php` deste Moodle. Se a identificação
  for ausente ou ambígua, mantém a interface original.

Os campos são administrados em Usuários > Contas > Campos de perfil do usuário.
As opções armazenadas dos menus são preservadas em português para não invalidar
dados existentes; os textos da interface têm traduções pt_br/en.
O ano máximo de nascimento é calculado na instalação/migração; nos anos
seguintes, atualize-o pela administração dos campos de perfil quando necessário.

## Arquitetura

- `classes/local/registration_setup.php`: migração única de configurações e campos.
- `classes/local/profile_fields.php`: definições de perfil e correções delimitadas.
- `classes/local/login_customization.php`: fornece a configuração do provedor ao tema; não carrega apresentação institucional.
- `styles.css`: apresentação básica independente, restrita aos componentes do plugin. Temas que declaram `coursecarouselpresentation = true` assumem seus estilos e templates.
- O módulo `login_custom`, sua logo e os testes de apresentação institucional pertencem agora ao tema UFMG Digital.
- `classes/local/course_url_resolver.php`: destino centralizado de todos os cartões.
- `classes/privacy/provider.php`: informa que os valores de perfil ficam em `core_user`.

O código em execução usa somente classes, recursos e configurações do
`local_coursecarousel`. Referências ao componente antigo estão limitadas à
migração, aos testes dessa migração/independência e a este documento.

## Preservação de dados e limites

Nenhum campo nem valor de usuário é excluído. Os IDs são reutilizados, incluindo
campos movidos de categoria quando registrados no antigo `managedfields`.
Sem esse registro, a migração reconhece os shortnames e tipos conhecidos dentro
da categoria “Dados Obrigatórios UFMG”. Campos de outras categorias com nomes
coincidentes são preservados. Campos públicos reconhecidos tornam-se privados;
campos já mais restritos não são ampliados. O antigo `pais_custom` é preservado.
Definições removidas por versões antigas não são recuperadas automaticamente.

CPF permanece um campo de texto, sem validação de dígitos ou unicidade nesta
funcionalidade. Não use CPF como chave para vincular identidades.
Autenticação, criação de conta, confirmação de e-mail, edição do perfil, regras
de acesso e matrícula continuam sob responsabilidade do Moodle e de seus plugins
específicos. Este plugin não cria matrículas automaticamente.
O retorno ao curso usa a sessão nativa; a confirmação de e-mail em outro navegador
pode perder esse destino. Não há armazenamento adicional em dados pessoais.

## Testes

Com o ambiente PHPUnit do Moodle inicializado, na raiz do Moodle:

    vendor/bin/phpunit local/coursecarousel/tests

Os testes cobrem migração de configurações, preservação de IDs/valores, repetição
da instalação, campos externos, privacidade e destinos nativos dos cartões para
matriculados, não matriculados, visitantes e convidados.

O teste JavaScript não exige dependências externas:

    node theme/ufmgdigital/tests/login_custom_test.cjs

Em homologação, verifique também: provedores múltiplos; URL incorreta; login
local/institucional; visitante e convidado; cadastro com confirmação de e-mail;
perfil incompleto; usuário matriculado e não matriculado; curso oculto; matrícula
indisponível; cartões da página inicial, catálogo e painel.

Referências das APIs usadas:
https://github.com/moodle/moodle/blob/MOODLE_401_STABLE/lib/moodlelib.php
https://github.com/moodle/moodle/blob/MOODLE_401_STABLE/user/profile/lib.php

## Instalação e importação de campos de cadastro por CSV

A instalação nova usa os três arquivos da pasta `install/`:
`categorias.csv`, `metadados.csv` e `cadastro.csv`. As definições de cadastro
deixaram de ficar fixas no PHP. A migração inicial de versões antigas usa o
mesmo CSV para reconhecer/criar campos, preservando os valores existentes.
Atualizar de 1.11.1 para 1.12.0 não reimporta nem sobrescreve os campos.

Nas configurações do plugin, “Cadastro e acesso UFMG” oferece links para
importar campos, administrar os campos nativos e baixar o modelo. A tela
“Importar categorias, metadados e cadastro” aceita qualquer combinação dos
três arquivos. Categorias e metadados mantêm o comportamento anterior.
Para importar cadastro é necessária a permissão `moodle/site:config`, além
da permissão de gerenciar o plugin. Não são importados dados de usuários.

O CSV de cadastro usa UTF-8, vírgulas e estes cabeçalhos:

    category,shortname,name,datatype,required,signup,visible,sortorder,options,maxlength,startyear,endyear

- `category`: categoria do perfil; criada se necessária para um campo novo.
- `shortname`: identificador único, até 100 caracteres, iniciando por letra
  minúscula; demais caracteres: letras minúsculas, números ou sublinhado.
- `name`: rótulo; `datatype`: `text`, `menu` ou `datetime`.
- `required` e `signup`: 0 ou 1; `sortorder`: inteiro de 1 a 999999.
- `visible`: 0 oculto; 1 privado; 2 público; 3 para quem pode ver a identidade.
  O modelo usa 1 para preservar a privacidade dos campos.
- `options`: itens de menu separados por `|`, sem itens vazios/repetidos.
- `maxlength`: limite do texto, de 1 a 255; vazio usa 255.
- `startyear` e `endyear`: limites de data de 1900 a 2100.
  `endyear=current` usa o ano da importação. Colunas não aplicáveis ficam vazias.

O importador valida todos os registros antes de gravar. Nomes repetidos dentro
do arquivo ou configurações inválidas interrompem a operação. A importação
conjunta é transacional. Campos cujo shortname já existe são contabilizados
como preservados; não mudam de categoria, tipo, visibilidade ou obrigatoriedade,
e seus valores de usuários permanecem intactos. Para alterar um campo existente,
use “Administrar campos de perfil”. Reimportar o modelo não duplica campos.

## Recriação automática de categorias e metadados a partir da 1.12.1

Em toda atualização real de versão, o plugin reaplica `install/categorias.csv`
e `install/metadados.csv` para criar os itens que estiverem ausentes.
Atualizar de 1.12.0 para 1.12.1 já executa essa restauração.

Campos existentes são identificados pelo shortname dentro dos metadados de
curso; não são sobrescritos. A categoria de metadados configurada é reutilizada
pelo ID, mesmo que tenha sido renomeada. Se tiver sido excluída, é recriada.
Categorias de cursos são identificadas por nome e categoria pai, preservando a
hierarquia do CSV. Se uma categoria for renomeada ou movida, o caminho original
do CSV será considerado ausente e poderá ser recriado; a categoria alterada
não é removida.

A operação recria definições, não recupera cursos ou valores de campos que
tenham sido excluídos. Também não regenera o menu personalizado.
Reenviar o mesmo ZIP sem aumentar a versão não executa uma atualização:
nesse caso, use a importação CSV disponível nas configurações.

Os testes de `tests/installer_test.php` cobrem recriação de metadados/categoria
de metadados, hierarquia de categorias de cursos, reexecução sem duplicação e
preservação de campos existentes.

## Recriação automática do cadastro a partir da 1.12.2

Toda atualização real de versão também reaplica `install/cadastro.csv`.
Ao atualizar de 1.12.1 para 1.12.2, os campos de perfil ausentes são recriados.
Se a categoria necessária para um campo novo não existir, ela também é criada.

A rotina usa a instalação sem as correções da migração antiga: campos já
existentes mantêm seus IDs, categoria, rótulos, opções, obrigatoriedade,
visibilidade e valores de usuários. Reexecutar não duplica os campos.
Somente as definições presentes no CSV incluído no pacote são recriadas;
campos de arquivos personalizados enviados anteriormente não são restaurados
automaticamente, a menos que também constem nesse CSV.

Os três arquivos — `categorias.csv`, `metadados.csv` e `cadastro.csv` — passam
a criar seus itens ausentes durante a atualização. Isso não recupera dados
excluídos e não ocorre ao reenviar o mesmo ZIP sem mudança de versão.

## 1.12.3

Corrige o botao Voltar de video.php para abrir a Pagina inicial com redirect=0, evitando o redirecionamento para Meus cursos.
