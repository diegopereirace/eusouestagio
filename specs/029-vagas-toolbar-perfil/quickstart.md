# Quickstart: Toolbar + perfil `/vagas` (029)

```bash
docker compose exec drupal drush cr
```

## Contas de teste (validação)

| Persona | Login | Senha | O que esperar no aside de `/vagas` |
|---------|-------|-------|-------------------------------------|
| Estudante (candidato) | `diegocandidato` | `Teste@123` | Avatar + **Diego Candidato** + subtítulo `UECE · Bacharelado` (sem % de cadastro) |
| Admin | `admin` | *(senha local do ambiente)* | Avatar + **admin** + `Usuário` (anexo perfil) |
| Empresa | `diegoempresa` | *(senha local)* | Avatar + nome fantasia + `Empresa` |
| Anônimo | — | — | **Sem** card lateral; lista central ~680px |

Reset rápido da senha do estudante:

```bash
docker compose exec drupal vendor/bin/drush.php user:password diegocandidato "Teste@123"
```

## A) Contador + ordenação (anônimo)

1. Abrir `/vagas` deslogado — ver “N vagas encontradas” + “Ordenar por”; **sem** card lateral; lista centralizada.
2. Selecionar “Mais antigas” — ordem `created` ASC entre o mesmo nível de Destaque.
3. Voltar “Mais recentes” — `created` DESC; Destaques continuam primeiro.
4. Filtrar via Hero (`?title=…`) — total muda com o filtro.

## B) Aside logado (estudante)

1. Login com `diegocandidato` / `Teste@123`.
2. Abrir `/vagas` — card à direita com avatar, nome e subtítulo básico.
3. Confirmar: **não** há porcentagem de cadastro, “Perfil completo” nem “Minhas atividades”.
4. Clique no card → `/painel/estudante/perfil`.
5. Layout: lista ocupa a largura restante; aside ~260px (como anexo perfil admin).

## C) Aside admin (anexo)

1. Login admin → `/vagas`.
2. Card à direita: avatar + `admin` + `Usuário`.
3. Cards da listagem esticam na coluna principal ao lado do aside.

## D) Isolamento

1. Home: cards laranja intactos.
2. Hero Search em `/vagas` intacto.
3. Sem toggle grid/lista; sem filtros esquerda Figma.
