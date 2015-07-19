<form action="<?php echo APP_WWW_URI; ?>task/edit/new" method="post" class="modal hide fade" id="myMemo">
  <div class="modal-header">
    <a class="close" data-dismiss="modal">&#10006;</a>
    <h3>New Memo</h3>
  </div>
  <div class="modal-body">
	  <input type="text" name="fulltxt" style="width:98%" placeholder="Type details">
	  <span class="help-block">@user 25/12 12:00 "memo title"</span>
	  <label class="checkbox">
	    <input name="public" type="checkbox" value="1" /> Make this task public (all users can see this)
	  </label>
  </div>
  <div class="modal-footer">
    <a href="#" class="btn" data-dismiss="modal">Close</a>
    <button type="submit" class="btn btn-primary">Save changes</button>
  </div>
</form>

<form action="<?php echo APP_WWW_URI; ?>task/project/new" method="post" class="modal hide fade" id="myProject">
  <div class="modal-header">
    <a class="close" data-dismiss="modal">&#10006;</a>
    <h3>New Project</h3>
  </div>
  <div class="modal-body">
	  <textarea type="text" name="fulltxt" style="width:98%;height:144px"></textarea>
	  <span class="help-block">
	  	@client 31/12 12:00 "project title" PO1234<br />
	  	&nbsp; @user 20/12 15:00 T1000<br />
	  	&nbsp; @user 22/12 18:00 R1000 E0.12<br />
	  	&nbsp; @user H1.5
	  </span>
  </div>
  <div class="modal-footer">
    <a href="#" class="btn" data-dismiss="modal">Close</a>
    <button type="submit" class="btn btn-primary">Next step &rarr;</button>
  </div>
</form>