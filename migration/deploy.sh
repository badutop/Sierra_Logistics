#!/usr/bin/env bash
#
# Déploie le front (export statique) et/ou le plugin+thème WordPress vers
# l'hébergement OVH, via SFTP (lftp). Usage :
#
#   cp migration/deploy.env.example migration/deploy.env   # une fois, à compléter
#   ./migration/deploy.sh front       # build + déploie uniquement le front -> www/
#   ./migration/deploy.sh wordpress   # vendorise Dompdf + déploie plugin/thème -> www/gestion/wp-content/
#   ./migration/deploy.sh all         # les deux, dans cet ordre
#
# Ne touche jamais www/gestion/ lors du déploiement du front (exclu
# explicitement du mirror), et ne touche jamais rien d'autre que les
# dossiers du plugin/thème lors du déploiement WordPress (jamais tout
# www/gestion/, pour ne pas écraser wp-config-local.php, les uploads, ou
# d'autres plugins/thèmes installés directement en production).

set -euo pipefail

HERE="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "$HERE/.." && pwd)"
ENV_FILE="$HERE/deploy.env"

if [ ! -f "$ENV_FILE" ]; then
	echo "Fichier manquant : $ENV_FILE (copiez deploy.env.example et complétez-le)." >&2
	exit 1
fi
# shellcheck disable=SC1090
source "$ENV_FILE"

: "${OVH_HOST:?OVH_HOST manquant dans deploy.env}"
: "${OVH_USER:?OVH_USER manquant dans deploy.env}"
: "${OVH_REMOTE_PATH:?OVH_REMOTE_PATH manquant dans deploy.env (ex: /www)}"
OVH_SFTP_PORT="${OVH_SFTP_PORT:-22}"

if ! command -v lftp >/dev/null 2>&1; then
	echo "lftp est requis (brew install lftp, ou apt install lftp)." >&2
	exit 1
fi

lftp_open_command() {
	if [ -n "${OVH_SSH_KEY:-}" ]; then
		echo "set sftp:connect-program 'ssh -a -x -i ${OVH_SSH_KEY}'
open -u ${OVH_USER}, sftp://${OVH_HOST}:${OVH_SFTP_PORT}"
	else
		: "${OVH_PASSWORD:?OVH_PASSWORD ou OVH_SSH_KEY requis dans deploy.env}"
		echo "open -u ${OVH_USER},${OVH_PASSWORD} sftp://${OVH_HOST}:${OVH_SFTP_PORT}"
	fi
}

deploy_front() {
	echo "==> Build du front (export statique)"
	( cd "$REPO_ROOT" && npm run build )

	if [ ! -d "$REPO_ROOT/out" ]; then
		echo "Le build n'a pas produit de dossier out/ (voir next.config.mjs, output: \"export\")." >&2
		exit 1
	fi

	echo "==> Déploiement du front vers ${OVH_REMOTE_PATH}/"
	lftp <<-EOF
	$(lftp_open_command)
	mirror --reverse --delete --verbose \
	  --exclude-glob gestion/ \
	  --exclude-glob .well-known/ \
	  "$REPO_ROOT/out/" "${OVH_REMOTE_PATH}/"
	bye
	EOF
	echo "==> Front déployé."
}

deploy_wordpress() {
	local plugin_dir="$REPO_ROOT/wordpress/wp-content/plugins/sierra-logistics-core"
	local theme_dir="$REPO_ROOT/wordpress/wp-content/themes/sierra-gestion"

	if ! command -v composer >/dev/null 2>&1; then
		echo "composer est requis pour vendoriser Dompdf avant le déploiement (le mutualisé OVH n'a pas Composer, voir migration/DEPLOIEMENT.md)." >&2
		exit 1
	fi

	echo "==> Installation des dépendances de production du plugin (Dompdf uniquement, sans phpunit/phpcs)"
	( cd "$plugin_dir" && composer install --no-dev --optimize-autoloader )

	echo "==> Déploiement du plugin vers ${OVH_REMOTE_PATH}/gestion/wp-content/plugins/sierra-logistics-core/"
	echo "==> Déploiement du thème vers ${OVH_REMOTE_PATH}/gestion/wp-content/themes/sierra-gestion/"
	lftp <<-EOF
	$(lftp_open_command)
	mirror --reverse --delete --verbose \
	  --exclude-glob tests/ \
	  --exclude-glob .git/ \
	  --exclude phpcs\.xml\.dist \
	  --exclude phpunit\.xml\.dist \
	  --exclude composer\.lock \
	  "$plugin_dir/" "${OVH_REMOTE_PATH}/gestion/wp-content/plugins/sierra-logistics-core/"
	mirror --reverse --delete --verbose \
	  "$theme_dir/" "${OVH_REMOTE_PATH}/gestion/wp-content/themes/sierra-gestion/"
	bye
	EOF

	echo "==> Réinstallation des dépendances de dev locales (phpunit/phpcs), pour ne pas casser votre environnement"
	( cd "$plugin_dir" && composer install )

	echo "==> Plugin et thème déployés. Dans wp-admin : rechargez la page des extensions, réactivez si WordPress le demande."
}

case "${1:-}" in
	front)
		deploy_front
		;;
	wordpress)
		deploy_wordpress
		;;
	all)
		deploy_front
		deploy_wordpress
		;;
	*)
		echo "Usage: $0 {front|wordpress|all}" >&2
		exit 1
		;;
esac
