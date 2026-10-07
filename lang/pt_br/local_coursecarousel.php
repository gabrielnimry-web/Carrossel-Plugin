<?php
// This file is part of Moodle - http://moodle.org/.
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Local Course Carousel plugin.
 *
 * @package    local_coursecarousel
 * @copyright  2026 Universidade Federal de Minas Gerais
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['about'] = 'Sobre';
$string['faq'] = 'Perguntas frequentes';
$string['contentpagesheading'] = 'Páginas Sobre e FAQ';
$string['contentpagesheading_desc'] = 'Edite o conteúdo das páginas institucionais sem alterar o código do plugin.';
$string['abouttitle_setting'] = 'Título da página Sobre';
$string['abouttitle_setting_desc'] = 'Título exibido no topo da página Sobre.';
$string['aboutcontent_setting'] = 'Conteúdo da página Sobre';
$string['aboutcontent_setting_desc'] = 'Use o editor para inserir e formatar o texto da página Sobre.';
$string['aboutcontent_default'] = '<p>Conheça mais sobre esta plataforma, seus objetivos e os conteúdos disponíveis.</p>';
$string['faqtitle_setting'] = 'Título da página FAQ';
$string['faqtitle_setting_desc'] = 'Título exibido no topo da página de perguntas frequentes.';
$string['faqcontent_setting'] = 'Conteúdo da página FAQ';
$string['faqcontent_setting_desc'] = 'Insira perguntas e respostas usando títulos, parágrafos e listas.';
$string['faqcontent_default'] =
    '<h2>Como acesso os cursos?</h2><p>Escolha um curso no catálogo e use o botão de acesso ' .
    'disponível na página do curso.</p><h2>Preciso estar conectado?</h2><p>Alguns conteúdos ' .
    'podem exigir autenticação ou inscrição.</p>';
$string['importcsv'] = 'Importar categorias, metadados e cadastro';
$string['openimportcsv'] = 'Abrir a tela de importacao de arquivos CSV';
$string['importcsvinfo'] =
    'Envie pelo menos um arquivo. A importacao cria categorias e campos que ainda nao existem; ' .
    'registros existentes sao preservados.';
$string['categoriescsv'] = 'Arquivo categorias.csv';
$string['categoriescsv_help'] =
    'CSV com cabeçalhos sequenciais categoria1,categoria2,... para quantos níveis da hierarquia ' .
    'forem necessários.';
$string['metadatacsv'] = 'Arquivo metadados.csv';
$string['metadatacsv_help'] = 'CSV com os cabecalhos shortname,name,type,description,sortorder,options.';
$string['selectatleastonecsv'] = 'Selecione pelo menos um arquivo CSV.';
$string['invalidcategoriescsv'] =
    'O arquivo de categorias deve conter cabeçalhos sequenciais categoria1,categoria2,... sem ' .
    'lacunas.';
$string['invalidcsvrow'] = 'A linha {$a} do CSV contém mais colunas que o cabeçalho.';
$string['invalidmetadatacsv'] = 'O arquivo de metadados nao possui todos os cabecalhos obrigatorios.';
$string['categoriesimported'] = 'Categorias importadas com sucesso.';
$string['metadataimported'] = 'Metadados importados com sucesso.';
$string['csvreaderror'] = 'Nao foi possivel ler o arquivo CSV.';

$string['pluginname'] = 'Carrossel de cursos';
$string['coursecarousel:manage'] = 'Gerenciar configurações e importações do Carrossel de cursos';
$string['updatemenu'] = 'Regenerar o menu personalizado';
$string['menuupdated'] = 'O menu personalizado do Moodle foi atualizado com sucesso.';
$string['generatedcontent'] = 'Conteúdo gerado';
$string['generalsettings'] = 'Comportamento geral';
$string['generalsettings_desc'] = 'Controle onde o plugin é exibido e como seus carrosséis se comportam.';
$string['enablehomepage'] = 'Exibir na página inicial do site';
$string['enablehomepage_desc'] = 'Exibe os carrosséis configurados de cursos e vídeos na página inicial do site.';
$string['enabledashboardcarousel'] = 'Exibir cursos disponíveis no painel';
$string['enabledashboardcarousel_desc'] = 'Carrega os cursos com autoinscrição disponíveis no topo do painel do usuário.';
$string['enablevlibras'] = 'Ativar VLibras';
$string['enablevlibras_desc'] = 'Carrega o widget externo VLibras nas páginas do Moodle.';
$string['itemspermobileslide'] = 'Cards por slide no celular';
$string['itemspermobileslide_desc'] = 'Número de cards agrupados em cada slide do carrossel em celulares. Temas com apresentação própria podem substituir esse agrupamento por rolagem contínua.';
$string['itemspertabletslide'] = 'Cards por slide no tablet';
$string['itemspertabletslide_desc'] = 'Número de cards agrupados em cada slide do carrossel em tablets. Temas com apresentação própria podem substituir esse agrupamento por rolagem contínua.';
$string['itemsperdesktopslide'] = 'Cards por slide no computador';
$string['itemsperdesktopslide_desc'] = 'Número de cards agrupados em cada slide do carrossel em computadores. Temas com apresentação própria podem substituir esse agrupamento por rolagem contínua.';
$string['durationfield'] = 'Nome breve do campo de carga horária';
$string['durationfield_desc'] = 'Nome breve do campo personalizado de curso usado como duração nos cards.';
$string['showbadges'] = 'Exibir badges dos cursos';
$string['showbadges_desc'] = 'Exibe o badge de categoria ou metadado nos cards e na página de detalhes do curso.';
$string['badgefield'] = 'Nome breve do campo de badge';
$string['badgefield_desc'] =
    'Campo personalizado de curso opcional usado como texto do badge. Deixe vazio para usar a ' .
    'hierarquia de categorias.';
$string['requireloginforcatalog'] = 'Exigir login no catálogo';
$string['requireloginforcatalog_desc'] = 'Quando ativado, somente usuários autenticados podem abrir o catálogo do plugin.';
$string['requireloginforvideos'] = 'Exigir login nas páginas de vídeo';
$string['requireloginforvideos_desc'] =
    'Quando ativado, somente usuários autenticados podem abrir o reprodutor de vídeos do ' .
    'plugin.';
$string['menucategories'] = 'Categorias no menu personalizado gerado';
$string['menucategories_desc'] =
    'Categorias de nível superior incluídas quando um administrador regenerar explicitamente o ' .
    'menu personalizado do Moodle. Se nenhuma for selecionada, todas as categorias de nível ' .
    'superior visíveis serão incluídas.';
$string['categories'] = 'Categorias';
$string['menutitle'] = 'Título do menu gerado';
$string['menutitle_desc'] = 'Texto usado no item principal do menu personalizado gerado para o Moodle.';
$string['menuabout'] = 'Incluir a página Sobre no menu';
$string['menuabout_desc'] = 'Adiciona um link para a página Sobre do plugin ao regenerar o menu.';
$string['menufaq'] = 'Incluir a página FAQ no menu';
$string['menufaq_desc'] = 'Adiciona um link para a página FAQ do plugin ao regenerar o menu.';
$string['updatemenuwarning'] =
    'Esta ação substitui todo o menu personalizado do Moodle pelo conteúdo gerado. Itens ' .
    'existentes de outras fontes serão removidos.';
$string['carouselcourses'] = 'Cursos exibidos';
$string['carouselcourses_desc'] =
    'Selecione cursos de uma ou mais categorias. A lista mostra Categoria / Curso. Se nenhum ' .
    'curso for selecionado, todos os cursos da categoria do carrossel serão considerados.';
$string['carouselcount'] = 'Quantidade de carrosséis de cursos';
$string['carouselcount_desc'] =
    'Escolha quantos slots configuráveis de carrossel serão processados na página inicial. ' .
    'Salve e recarregue esta página para editar novos slots.';
$string['carouselheading'] = 'Carrossel de cursos {$a}';
$string['carouselenabled'] = 'Ativar carrossel de cursos {$a}';
$string['carouseltitle'] = 'Título do carrossel de cursos {$a}';
$string['carouseltitle_desc'] = 'Deixe vazio para usar o nome da categoria selecionada.';
$string['carouselcategory'] = 'Categoria do carrossel de cursos {$a}';
$string['carouselcategory_desc'] = 'São considerados os cursos desta categoria e de suas subcategorias.';
$string['carousellimit'] = 'Quantidade máxima de cursos';
$string['carousellimit_desc'] = 'Quantidade máxima de cursos renderizados neste carrossel.';
$string['search'] = 'Buscar';
$string['searchplaceholder'] = 'Busque por curso ou metadado';
$string['filterbymetadata'] = 'Filtrar por metadados';
$string['anyvalue'] = 'Todos';
$string['applyfilters'] = 'Aplicar filtros';
$string['clearfilters'] = 'Limpar filtros';
$string['showall'] = 'Mostrar todos';
$string['availablecontent'] = 'Conheça todos os conteúdos disponíveis.';
$string['aboutcourse'] = 'Sobre este curso';
$string['availablecourses'] = 'Cursos disponíveis';
$string['noresults'] = 'Nenhum conteúdo encontrado';
$string['noresultshelp'] = 'Tente outro termo ou consulte uma categoria diferente.';
$string['welcomeback'] = 'Bem-vindo de volta, {$a}!';
$string['dashboardintro'] = 'Continue de onde você parou e explore novos conteúdos.';
$string['video'] = 'Vídeo';
$string['videos'] = 'Vídeos';
$string['videosenabled'] = 'Ativar carrossel de vídeos';
$string['videosenabled_desc'] = 'Exibe o carrossel de vídeos e consulta as playlists configuradas do YouTube e do Eduplay.';
$string['youtubeenabled'] = 'Ativar vídeos do YouTube';
$string['youtubeenabled_desc'] = 'Consulta vídeos da playlist configurada do YouTube.';
$string['eduplayenabled'] = 'Ativar vídeos do Eduplay';
$string['eduplayenabled_desc'] = 'Consulta vídeos da playlist configurada do Eduplay.';
$string['youtubeapikey'] = 'Chave da API do YouTube';
$string['youtubeapikey_desc'] = 'Credencial da YouTube Data API v3. Ela pode ser definida de forma segura pelo config.php.';
$string['eduplayclientkey'] = 'Client Key do Eduplay';
$string['eduplayclientkey_desc'] = 'Credencial de cliente da API Eduplay. Ela pode ser definida de forma segura pelo config.php.';
$string['youtubeplaylist'] = 'Playlist do YouTube';
$string['youtubeplaylist_desc'] = 'Identificador da playlist do YouTube exibida pelo plugin.';
$string['eduplayplaylist'] = 'Playlist do Eduplay';
$string['eduplayplaylist_desc'] = 'Identificador da playlist do Eduplay exibida pelo plugin.';
$string['youtubemaxvideos'] = 'Máximo de vídeos do YouTube';
$string['eduplaymaxvideos'] = 'Máximo de vídeos do Eduplay';
$string['providermaxvideos_desc'] = 'Quantidade máxima de vídeos consultada neste provedor.';
$string['videotimeout'] = 'Timeout das APIs de vídeo';
$string['videotimeout_desc'] = 'Quantidade máxima de segundos permitida para cada chamada às APIs externas de vídeo.';
$string['videostitle_setting'] = 'Título da seção de vídeos';
$string['videostitle_setting_desc'] = 'Título exibido acima do carrossel de vídeos.';
$string['watchvideo'] = 'Assistir ao vídeo {$a}';
$string['openvideofullscreen'] = 'Abrir o vídeo {$a} em tela ampla';
$string['openfullscreen'] = 'Assistir em tela ampla';
$string['course'] = 'Curso';
$string['catalogtitle'] = 'Catálogo de cursos';
$string['catalogpagesize'] = 'Cursos por página do catálogo';
$string['catalogpagesize_desc'] = 'Quantidade máxima de cards de curso renderizados em cada página do catálogo.';
$string['catalogloaderror'] = 'Não foi possível carregar os cursos no momento.';
$string['catalogloaddebug'] = 'Erro ao carregar cursos no catálogo: {$a}';

$string['institutionallogin'] = 'Acessar com MinhaUFMG';
$string['customizelogin'] = 'Personalizar acesso MinhaUFMG';
$string['customizelogin_desc'] = 'Disponibiliza ao tema a configuração do provedor institucional. A personalização visual depende do suporte do tema ativo e preserva os outros métodos de acesso.';
$string['identityproviderurl'] = 'URL do provedor MinhaUFMG';
$string['identityproviderurl_desc'] = 'Informe o endereço exato do link institucional exibido no login, incluindo parâmetros. Em branco, identifica apenas /auth/shibboleth/index.php deste Moodle. Se nenhum link ou mais de um corresponder, a interface original será preservada.';
$string['privacy:metadata:core_user'] = 'Os valores dos campos de cadastro são armazenados e gerenciados pelo subsistema de perfil de usuários do Moodle.';

$string['registrationsettings'] = 'Cadastro e acesso UFMG';
$string['registrationsettings_desc'] = 'Cadastro, campos de perfil e personalização do login fazem parte deste plugin. Os campos são administrados em Usuários > Contas > Campos de perfil do usuário.';

$string['profiletools'] = 'Campos de cadastro por CSV';
$string['importprofilefields'] = 'Importar campos de cadastro';
$string['manageprofilefields'] = 'Administrar campos de perfil';
$string['downloadprofilecsv'] = 'Baixar modelo cadastro.csv';
$string['profilecsv'] = 'Arquivo cadastro.csv';
$string['profilecsv_help'] = 'CSV UTF-8 separado por vírgulas com cabeçalhos category,shortname,name,datatype,required,signup,visible,sortorder,options,maxlength,startyear,endyear. Tipos: text, menu e datetime. required/signup: 0 ou 1. visible: 0 (oculto), 1 (privado), 2 (público), 3 (permissão de identidade). sortorder: inteiro positivo. Opções de menu separadas por |. Texto: maxlength entre 1 e 255. Data: anos entre 1900 e 2100; endyear aceita current. Campos existentes são preservados. A importação de cadastro exige permissão de configuração do site.';
$string['invalidprofilecsv'] = 'O CSV de cadastro deve conter os 12 cabeçalhos do modelo e pelo menos um campo.';
$string['invalidprofilecsvrow'] = 'O registro {$a} do CSV de cadastro é inválido. Confira nomes únicos, tipo, opções, limites, anos e indicadores conforme o modelo.';
$string['profilefieldsimported'] = 'Cadastro: {$a->created} campos criados; {$a->skipped} campos existentes preservados.';
$string['invalidcsvheaders'] = 'O CSV contém cabeçalhos vazios ou repetidos.';

$string['enrolcourse'] = 'Inscreva-se';
$string['accesscourse'] = 'Acessar curso';
$string['moreinformation'] = 'Mais informações';
$string['close'] = 'Fechar';
$string['nosummary'] = 'Este curso ainda não possui informações adicionais.';