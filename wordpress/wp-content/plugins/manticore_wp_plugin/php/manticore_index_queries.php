<?php
/*
    Manticore Search Plugin (contact@manticoresearch.com), 2018
    If you need commercial support, or if you’d like this plugin customized for your needs, we can help.

    Visit our website for the latest news:
    https://manticoresearch.com/

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Office 2, Derby House, 123 Watling Street, Gillingham, Kent, ME7 2YY England
*/

return [
	'query_posts'          => 'select (p.ID+1)*({blog_id}+{shards_count}) as ID, {blog_id} as blog_id, 0 as comment_ID, p.ID as post_ID, p.post_title as title, p.post_content as body,
            t.name as category, IF(p.post_type = \'post\', 1, 0) as isPost, 0 as isComment,
            IF(p.post_type = \'page\', 1, 0) as isPage, IF(p.post_type = \'post\', 0,
            IF(p.post_type = \'page\', 1, 2)) as post_type, UNIX_TIMESTAMP(post_date) AS date_added,
            GROUP_CONCAT(DISTINCT tag_t.name) as tags,
  			GROUP_CONCAT(DISTINCT taxonomy_t.name, \' |*| \', taxonomy_tt.taxonomy SEPARATOR \'\n\') as taxonomy,
  			GROUP_CONCAT(DISTINCT meta.meta_value, \' |*| \', meta.meta_key SEPARATOR \'\n\')        as custom_fields
        from
            {table_prefix}posts as p
        left join
            {table_prefix}term_relationships tr on (p.ID = tr.object_id)
        left join
            {table_prefix}term_taxonomy tt on (tt.term_taxonomy_id = tr.term_taxonomy_id and tt.taxonomy = \'category\')
        left join
            {table_prefix}terms t on (tt.term_id = t.term_id)
        left join
            {table_prefix}term_relationships tag_tr on (p.ID = tag_tr.object_id)
        left join
            {table_prefix}term_taxonomy tag_tt on (tag_tt.term_taxonomy_id = tag_tr.term_taxonomy_id and tag_tt.taxonomy = \'post_tag\')
        left join
            {table_prefix}terms tag_t on (tag_tt.term_id = tag_t.term_id)
        left join
  			{table_prefix}term_taxonomy taxonomy_tt on (taxonomy_tt.term_taxonomy_id = tag_tr.term_taxonomy_id and taxonomy_tt.taxonomy in ({index_taxonomy}))
	    left join
	  		{table_prefix}terms taxonomy_t on (taxonomy_tt.term_id = taxonomy_t.term_id)	  
	  	left join
  			{table_prefix}postmeta meta on (p.ID = meta.post_id and meta.meta_key in ({index_custom_fields}))
        where
            p.ID in ({in_ids}) group by p.ID',
	'query_posts_count'    => 'select COUNT(DISTINCT (p.ID)) as cnt from {table_prefix}posts as p where p.post_status = \'publish\' and p.post_type in ({index_post_types})',
	'query_posts_ids'    => 'select p.ID as cnt from {table_prefix}posts as p where p.post_status = \'publish\' and p.post_type in ({index_post_types}) LIMIT {limit} ',
	'query_comments'       => 'select c.comment_ID*({blog_id}+{shards_count}) as ID, {blog_id} as blog_id, c.comment_ID as comment_ID,
    c.comment_post_ID as post_ID, \'\' as title, c.comment_content as body,
    \'\' as category, 0 as isPost, 1 as isComment,0 as isPage, 2 as post_type,
    UNIX_TIMESTAMP(comment_date) AS date_added, \'\' as tags, \'\' as taxonomy, \'\' as custom_fields
    from {table_prefix}comments as c
    where c.comment_approved = \'1\' LIMIT {limit}',
	'query_comments_count' => 'select COUNT(*) as cnt from {table_prefix}comments as c where c.comment_approved = \'1\' ',
	'query_stats'          => 'select id, keywords, status, crc32(keywords) as keywords_crc, UNIX_TIMESTAMP(date_added) as date_added from {table_prefix}sph_stats LIMIT {limit}',
	'query_stats_count'    => 'select COUNT(id) as cnt from {table_prefix}sph_stats',
	'query_attachments'    => 'select * from {table_prefix}posts WHERE post_type = \'attachment\' and post_mime_type NOT IN ({skip_indexing_mime_types}) LIMIT {limit}',
	'query_attachments_count'    => 'select COUNT(id) as cnt from {table_prefix}posts WHERE post_type = \'attachment\' and post_mime_type NOT IN ({skip_indexing_mime_types})'
];
