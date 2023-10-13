<?xml version="1.0" encoding="utf-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
	@foreach($pages as $page)
	
	<url>
		<loc>{{ config('app.url')."/".$page->page_name.".html" }}</loc>
		<changefreq>daily</changefreq>
		<priority>0.90</priority>
	</url>
	
	@endforeach
	
	@foreach($blogs as $blog)
	<url>
		<loc>{{ config('app.url')."/blog/".$blog->seo_url.".html" }}</loc>
		<changefreq>daily</changefreq>
		<priority>1.00</priority>
	</url>
	@endforeach
</urlset>