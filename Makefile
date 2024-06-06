PATH_TO_REPO := $(shell git rev-parse --show-toplevel )

all: install

install:
	mkdir -p /usr/local/cpanel/base/frontend/jupiter/varnish_cache_flusher
	cp plugin/*.php /usr/local/cpanel/base/frontend/jupiter/varnish_cache_flusher/
	/usr/local/cpanel/scripts/install_plugin ${PATH_TO_REPO}/plugin --theme jupiter
	/usr/local/cpanel/bin/servers_queue run

uninstall:
	/usr/local/cpanel/scripts/uninstall_plugin ${PATH_TO_REPO}/plugin --theme=jupiter
	[ -d /usr/local/cpanel/base/frontend/jupiter/varnish_cache_flusher ] && rm -rf /usr/local/cpanel/base/frontend/jupiter/varnish_cache_flusher
	/usr/local/cpanel/bin/servers_queue run
