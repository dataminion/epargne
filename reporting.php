<?php session_start();
	if(!$_SESSION['islogin'])
	{
		include "template.php";
		$template= new template('html');
		$template->set_filenames(array(
			'login' => 'login.html')
		);
		$template->assign_block_vars('switch_login_fails',	array());
		$template->pparse('login');
		$template->set_filenames(array(
			'footer' => 'footer.html')
		);
		$template->pparse('footer');
		exit();
	}
		include'classes/my_customers.php';
	$data=array();
	$List=new client();
	
	?>
	
	 <div class="verify">
    <form action = "confirm.php" method="POST">

      <fieldset>
      	<legend>
			Donnee a Verifier
		</legend>
		<table class="tabl" width="100%" cellspacing="0" summary="Data set of deposits to be approved from a given day.">
          <caption>
          <a href="#" onClick="return displayMenu('30 -- 9 -- 2009');">30 -- 9 -- 2009</a>
          </caption>

          <thead class="hat" id="30 -- 9 -- 20091">
            <tr>
              <th>Utiliseur</th>
              <th>ID</th>
              <th>Nom</th>
              <th>Mise</th>
              <th>Deposer</th>

              <th>Selectioner</th>
            </tr>
          </thead>
          <tfoot class="hat" id="30 -- 9 -- 20093">
            <tr>
              <th colspan="6">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
            </tr>
          </tfoot>

          <tbody class="hat" id="30 -- 9 -- 20092">
            <tr>
              <th class="empty" colspan="6"><h3><a href="#" onClick="return displayZone('30 -- 9 -- 20091');">ZONE : 1</a></h3></th>
            </tr>
	<?php
			$List->query("CREATE TEMPORARY TABLE recent_date_zone SELECT MAX( DATE ) AS max_date, CUST_ID, ZONE
				FROM cust_location
				GROUP BY CUST_ID
				ORDER BY ZONE DESC;");
			$List->query("SET @rank =0,
				@prev_val = 1;");
			$List->query("CREATE TEMPORARY TABLE current_zone 
				SELECT @rank := IF( @prev_val = ZONE , @rank+1, 1 ) AS rank, @prev_val := ZONE AS ZONE, CUST_ID
				FROM recent_date_zone
				ORDER BY max_date, ZONE DESC;");
			$List->query("CREATE TEMPORARY TABLE goal_date
				SELECT MAX( cust_goal.DATE ) AS max_date, cust_goal.CUST_ID, current_zone.rank, current_zone.ZONE
				FROM cust_goal, deposit, current_zone
				WHERE cust_goal.CUST_ID=deposit.CUST_ID
				AND cust_goal.CUST_ID=current_zone.CUST_ID
				GROUP BY CUST_ID;");		
			$List->query("SELECT SUM( deposit.AMOUNT ) AS TDEPOSIT, deposit.CUST_ID, goal_date.rank, goal_date.ZONE 
				FROM deposit, goal_date
				WHERE deposit.CUST_ID = goal_date.CUST_ID
				GROUP BY CUST_ID;");
			while($resultie = mysql_fetch_array($List->result)){
				if (mysql_errno ( )){
					die (sprintf ("Cannot connect to server: %s (%d)\n", htmlspecialchars (mysql_error ( )),	mysql_errno ( )));
				}
				?>
				<tr class="noline" id="30 -- 9 -- 2009z1">

				<?php
				
				echo"<td>";
				echo$resultie[CUST_ID];
				echo"</td><td>";
				echo$resultie[TDEPOSIT];
				echo"</td>";
				
				?>
				<td>
					<input type="checkbox" name="check3" id="checkbox" />
					<input type="hidden" name="tran3" value="6306" />
					<input type="hidden" name="goal3" value="200" />
				</td>
            </tr>
				<?php
				
			}		
?>
                </tbody>
	  </table>
		<table class="tabl" width="100%" cellspacing="0" summary="Data set of deposits to be approved from a given day.">
          <caption>

          <a href="#" onClick="return displayMenu('1 -- 10 -- 2009');">1 -- 10 -- 2009</a>
          </caption>

          <thead class="hat" id="1 -- 10 -- 20091">
            <tr>
              <th>Utiliseur</th>
              <th>ID</th>
              <th>Nom</th>

              <th>Mise</th>
              <th>Deposer</th>
              <th>Selectioner</th>
            </tr>
          </thead>
          <tfoot class="hat" id="1 -- 10 -- 20093">
            <tr>

              <th colspan="6">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
            </tr>
          </tfoot>
          <tbody class="hat" id="1 -- 10 -- 20092">
            <tr>
              <th class="empty" colspan="6"><h3><a href="#" onClick="return displayZone('1 -- 10 -- 20091');">ZONE : 1</a></h3></th>
            </tr>
            <tr class="noline" id="1 -- 10 -- 2009z1">

				<td>batoure</td>
				<td><a href='index.php?pageload=7&cid=1'>1</a></td>
				<td>djihnto jeannette</td>
				<td>500</td>
				<td>500</td>
				<td>

					<input type="checkbox" name="check4" id="checkbox" />
					<input type="hidden" name="tran4" value="6315" />
					<input type="hidden" name="goal4" value="500" />
				</td>
            </tr>
          </tbody>
        </table>
      </fieldset>
   <fieldset>

      <h3>Qu'est que vous voulez faire avec ces donne:</h3>

         <input type="hidden" name="nTrans" value="4" />
        <input class="buttons" id="delete" type="submit" name="delete" value = "Supprimer" onclick="return confirmLink(this, 'suprimer')"   src="img/ico/delete.png"/>
      <!-- <input type="submit" value = "Editer" name="edit" onclick="return confirmLink(this, 'editer')" align="right" src="formatting/img/edit.png"/> -->
        <input class="buttons" id="ok" type="submit"  name="confirm" value = "Valider" onclick="return confirmLink(this, 'verifier')"src="img/ico/ok.png" />
      </fieldset>
      </div>
    </form>

            </div>
