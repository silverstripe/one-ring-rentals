<% include Banner %>
<div class="content">
	<div class="container">
		<div class="row">
			<div class="main col-sm-8">						
				<article>
					<h1>$Title</h1>

					<% if $FeaturedImage %>
						<p class="post-image">$FeaturedImage.setWidth(795)</p>
					<% end_if %>

					<div class="content">$Content</div>

					<% include EntryMeta %>
				</article>
			</div>
			
			<div class="sidebar gray col-sm-4">
				<% include BlogSideBar %>
			</div>
		</div>
	</div>
</div>