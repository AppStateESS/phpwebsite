<script type="text/javascript">
function confirmDelete(category_id){
  if (confirm('{QUESTION}'))
    location.href = "index.php?module=categories&action=admin&subaction=deleteCategory&category_id=" + category_id;
}
</script>
