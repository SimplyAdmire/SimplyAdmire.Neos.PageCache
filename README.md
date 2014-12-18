SimplyAdmire.Neos.PageCache
===========================

Example nginx rewrite rules

	error_page 418 = @uncached;

	location / {

		if ($http_cookie = 'TYPO3_Flow_Session') {
			return 418;
		}

		if ($request_method !~ ^(GET|HEAD)$ ) {
			return 418;
		}

		try_files /_Resources/Cache/SimplyAdmire_Neos_PageCache_PageCache/${host}${request_uri}index.html /_Resources/Cache/SimplyAdmire_Neos_PageCache_PageCache/${host}${request_uri}/index.html $uri $uri/ /index.php?$args;
	}

	location ~ \.php$ {
		include fastcgi_params;
		fastcgi_pass unix:/opt/local/var/run/php55/php5-fpm-flow.sock;
		fastcgi_index index.php;

		fastcgi_param FLOW_CONTEXT Production;
		fastcgi_param FLOW_REWRITEURLS 1;

		fastcgi_split_path_info ^(.+\.php)(.*)$;
		fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
		fastcgi_param PATH_INFO $fastcgi_path_info;
	}

	location @uncached {
		try_files $uri $uri/ /index.php?$args;
	}
