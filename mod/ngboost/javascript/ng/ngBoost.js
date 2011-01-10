
/**
  * @author Hilmar Runge <ngwebsite.net>
  * @version 20110108
  */
	
	var ngaximg = '<img src="' + source_http + 'mod/ngboost/img/ajax10red.gif" alt="..." />';
	var ngokimg = '<img src="' + source_http + 'mod/ngboost/img/ok.10.gif" alt="ok" />';
	var ngohimg = '<img src="' + source_http + 'mod/ngboost/img/oh.10.gif" alt="oh" />';
	var ngkoimg = '<img src="' + source_http + 'mod/ngboost/img/ko.10.gif" alt="ko" />';
	var ngnoimg = '<img src="' + source_http + 'mod/ngboost/img/no.10.gif" alt="no" />';
	var ngclose = '<div style="text-align:right;">'
				+ '<img id="ngjqmclose" class="jqmClose" src="' + source_http + 'mod/ngboost/img/close.16.gif" alt=" X " />'
				+ '</div>';

	var ngprobar = 0;
	
	// for actions to complete in sync:
	// ffu
	var ngsync = true;

	$(document).ready(function(){
	
		$("#ngbstcptabs").tabs();
		
		$('.ngAnyHelp').click(function(){
			var who = $(this).parent('div').parent('div').attr('id');
			ngAnyHelp(who);
		});

		$('.ngbsttab').click(function(){
			$('#ngbstcpfoot').html(' ');
		});

	});
	
	function ngAbout(mod) {
		ngUniCS(mod,'a');
	}
	function ngAnyHelp(id) {
		ngUniCS(id,'h');
	}
	function ngBu(mod) {
		var wo = '#ngmsgt11' + mod;
		$(wo).html(ngaximg);
		ngUniCS(mod,'b');
	}
	function ngBuSC(reply) {
		var ret = reply.split('--',2);
		var mod = ret[0];
		var cnt = ret[1] + '<br />';
		var wo = '#ngmsgt11' + mod;
		$(wo).html('Backup' + ngokimg);
		$('#ngbstcpfoot').append(cnt);
	}
	function ngBuAll() {
		ngUniCS('','B');
	}
	function ngBuAllSCx(reply) {
		// the list of mods
		var x;
		var ret = reply.split('--');
		ngprobar = ret.length;
		for (x in ret) {
			var wo = '#ngmsgt11' + ret[x];
			$(wo).html(ngaximg);
			ngUniCS(ret[x],'B2');
		}
	}
	function ngBuAllSC(reply) {
		var ret = reply.split('--',2);
		var mod = ret[0];
		var cnt = ret[1];
		var wo1 = '#ngmsgt11' + mod;
		ngPro();
		$(wo1).html('Backup' + ngokimg);
		$('#ngbstcpfoot').append(cnt + '<br />');
	}
	function ngBuBranch() {
		ngUniCS('core1v1','Br');
	}
	function ngCheck(mod) {
		var wo = '#ngchk' + mod;
		$(wo).html(ngaximg);
		ngUniCS(mod,'c');
	}
	function ngCheckSC(reply) {
		var ret = reply.split('--',3);
		var mod = ret[0];
		var vsn = ret[1];
		var cnt = ret[2];
		var wo1 = '#ngchk' + mod;
		$(wo1).text(vsn);
		ngUniJqSC(cnt,'#ngmsg');
	}
	function ngCheckAll() {
		ngUniCS('','C');
	}
	function ngCheckAllSCx(reply) {
		// the list of mods
		var x;
		var ret = reply.split('--');
		ngprobar = ret.length;
		for (x in ret) {
			var wo = '#ngchk' + ret[x];
			$(wo).html(ngaximg);
			ngCheckAllCSx(ret[x]);
		}
	}
	function ngCheckAllCSx(mod) {
		$.ajax({
			type: "GET",
			url: "ngboost/action/admin/xaop/c/p/" + mod + '/authkey/' + authkey,
			success: function(reply) {
						ngCheckAllSC(reply);
					}
		});
	}
	function ngCheckAllSC(reply) {
		var ret = reply.split('--',3);
		var mod = ret[0];
		var vsn = ret[1];
		var cnt = ret[2];
		var wo1 = '#ngchk' + mod;
		ngPro();
		$(wo1).text(vsn);
	}
	function ngInstall(mod) {
		ngUniCS(mod,'i');
	}	
	function ngInstallSC(reply) {
		var ret = reply.split('--',4);
		var cc = ret[0];
		var mod = ret[1];
		var flip = ret[2];
		var cnt = ret[3];
		var wo = '#ngin' + mod;
		var oldhref = $(wo).attr('href');
		var newhref = oldhref.replace('ngInstall','ngUnInstall');
		$('#ngmsg').html(cnt);
		$('#ngmsg').jqm();
		$('#ngmsg').jqmShow();
		if (cc == 'ok') {
			$(wo).text(flip);
			$(wo).attr('href', newhref);
			$(wo).attr('id', 'ngun' + mod);
		}
	}
	function ngUnInstall(p) {
		var wo = '#ngun' + p;
		var msg = 'are you sure to uninstall <b>' + p + '</b>';
		$(wo).fastConfirm({
			questionText: msg,
			onProceed: function(trigger) {ngUnInstallReplied(p,true);},
			onCancel: function(trigger) {ngUnInstallReplied(p,false);} 
		});
		// anything more here will run due fastconfirm is still not processed
	}	
	function ngUnInstallReplied(p,answer) {
		if (answer) {
			// .. bu fs
			// .. bu db
			ngUnInstallCS(p);
		} else {
			$('#ngmsg').html('<b>' + p + '</b> not uninstalled');
			$('#ngmsg').jqm();
			$('#ngmsg').jqmShow();
		}
	}
	function ngUnInstallCS(mod) {
		$.ajax({
			type: "GET",
			url: "ngboost/action/admin/xaop/un/p/" + mod + '/confirm/' + mod + '/authkey/' + authkey,
			success: function(reply) {
						ngUnInstallSC(reply);
					}
		});
	}
	function ngUnInstallSC(reply) {
		var ret = reply.split('--',4);
		var cc = ret[0];
		var mod = ret[1];
		var flip = ret[2];
		var cnt = ret[3];
		var wo = '#ngun' + mod;
		$('#ngmsg').html(cnt);
		$('#ngmsg').jqm();
		$('#ngmsg').jqmShow();
		if (cc == 'ok') {
			var oldhref = $(wo).attr('href');
			var newhref = oldhref.replace('ngUnInstall','ngInstall');
			$(wo).text(flip);
			$(wo).attr('href', newhref);
			$(wo).attr('id', 'ngin' + mod);
		}
	}
	function ngUpdate(mod) {
		ngUniCS(mod,'up');
	}	
	function ngUpdateSC(reply) {
		var ret = reply.split('--',3);
		var cc = ret[0];
		var mod = ret[1];
		var cnt = ret[2];
		var wo = '#ngup' + mod;
		$('#ngmsg').html(cnt);
		$('#ngmsg').jqm();
		$('#ngmsg').jqmShow();
		if (cc == 'ok') {
			// need removal of update
			// cannot be tested yet ..........TODO
			// $(wo + ":contains('|')").empty();
			$(wo).remove();
		}
	}
	function ngShowDep(mod) {
		ngUniCS(mod,'sd');
	}
	function ngShowDepUpon(mod) {
		ngUniCS(mod,'su');
	}
	function ngPickup(mod) {
		ngUniCS(mod,'wg');
	}	
	function ngPickupSC(reply) {
		var ret = reply.split('--',2);
		var mod = ret[0];
		var cnt = ret[1];
		var wo = '#ngpickup' + mod;
		$(wo).html(cnt + ' ' + ngokimg + '<br /><br />');
	}

	function ngSetSrc(reply) {
		var wo = '#ngbstcpfoot';
		$(wo).html(reply);
	}

	function ngUniCS(ref,op) {
		var url = 'ngboost/action/admin/xaop/';
		switch (op) {
			case 'a':
				url = url + 'a/p/';
				break;
			case 'b': case 'B2':
				url = url + 'bm/p/';
				break;
			case 'B':
				url = url + 'B';
				break;
			case 'Br':
				url = url + 'bm/p/';
				break;
			case 'c':
				url = url + 'c/p/';
				break;
			case 'C':
				url = url + 'C';
				break;
			case 'h':
				url = url + 'h/h/';
				break;
			case 'i':
				url = url + 'in/p/';
				break;
			case 'sd':
				url = url + 'd/p/';
				break;
			case 'su':
				url = url + 'u/p/';
				break;
			case 'up':
				url = url + 'up/p/';
				break;
			case 'wg':
				url = url + 'tget/m/';
				break;
			case 'ws':
				url = url + 'tS/p/';
				break;
			default:
				return;
		}
		url = url + ref + '/authkey/' + authkey; 
		$.ajax({
			type: "GET",
			url: url,
			success: function(reply) {
						switch (op) {
							case 'a':
								ngUniJqSC(reply,'#ngmsg');
							break;
							case 'b': case 'Br':
								ngBuSC(reply);
							break;
							case 'B':
								ngBuAllSCx(reply);
							break;
							case 'B2':
								ngBuAllSC(reply);
							break;
							case 'c':
								ngCheckSC(reply);
							break;
							case 'C':
								ngCheckAllSCx(reply);
							break;
							case 'h':
								ngUniJqSC(reply,'#ngpar');
							break;
							case 'i':
								ngInstallSC(reply);
							break;
							case 'sd':
								ngUniJqSC(reply,'#ngmsg');
							break;
							case 'su':
								ngUniJqSC(reply,'#ngmsg');
							break;
							case 'up':
								ngUpdateSC(reply);
							break;
							case 'wg':
								ngPickupSC(reply);
							break;
							case 'ws':
								ngSetSrc(reply);
							break;
						}
					}
		});
	}
	function ngUniJqSC(reply,to) {
		if (ngVerReply(reply)) {
			$(to).html(reply);
			$(to).jqm();
			$(to).jqmShow();
		}
	}
		
	function ngPro() {
		if (ngprobar > 1 ) {
			ngprobar = ngprobar - 1;
			$('#ngmsg').removeClass('jqmWindow');
			$('#ngmsg').attr('style', 'display:inline');
			$('#ngmsg').html(ngaximg + ' just <b>' + ngprobar + '</b> modules to process');
		} else {
			ngprobar = 0;
			$('#ngmsg').attr('style', 'display:none');
			$('#ngmsg').addClass('jqmWindow');
			$('#ngmsg').html(' ');
		}
	}
	




	function ngPlain(op) {
		ngPlainCS(op);
	}
	function ngPlainCS(op) {
		var fb = '#ngmsgt51';
		switch (op) {
		case 'ldb':
			fb = '#ngmsgt61';
			break;
		case 'ts':
			fb = '#ngmsgt71';
			break;
		}
		$.ajax({
			type: "GET",
			url: "ngboost/action/admin/xaop/" + op + "/authkey/" + authkey,
			success: function(reply) {
						ngPlainSC(reply,fb);
					}
		});
	}
	function ngPlainSC(reply,wo) {
		$(wo).html(reply);
	}

	function ngPop(op,k,v) {
		if (op == 'dy') {
			var wo = '#ngop' + v;
			var msg = 'Do you really want to purge the backup file?';
			$(wo).fastConfirm({
				questionText: msg,
				onProceed: function(trigger) {ngPopCS(op,k,v);},
				onCancel: function(trigger) {return;} 
			});
			return;
		}
		if (op == 're') {
			var wo = '#ngop' + v;
			var msg = 'Do you really want to restore / overwrite?';
			$(wo).fastConfirm({
				questionText: msg,
				onProceed: function(trigger) {ngPopCS(op,k,v);},
				onCancel: function(trigger) {return;} 
			});
			return;
		}
		ngPopCS(op,k,v);
	}
	function ngPopCS(op,k,v) {
		$.ajax({
			type: "GET",
			url: "ngboost/action/admin/xaop/" + op + "/" + k + "/" + v + "/authkey/" + authkey,
			success: function(reply) {
						ngPopSC(reply);
					}
		});
	}
	function ngPopSC(reply) {
		// may be #htmlid--msg--reply or only the reply
		var cnt = reply;
		var ret = reply.split('--',3);
		if (ret.length > 2 && cnt.substr(0,1)=='#') {
			var aid = ret[0];
			var msg = ret[1];
			cnt = ret[2];
			$(aid).html(msg);
		}
		$('#ngmsg').html(cnt);
		$('#ngmsg').jqm();
		$('#ngmsg').jqmShow();
	}

	

	function ngBuT(tn,mod) {
		var wo = '#ngt6t' + tn;
		$(wo).html(ngaximg);
		ngBuTCS(tn,mod);
	}
	// ---(
	function ngBuTs1(op,fgsrc) {
		ngBuTCSs1(op,fgsrc);
	}
	function ngBuTCSs1(op,fgsrc) {
		$.ajax({
			type: "GET",
			url: "ngboost/action/admin/xaop/bt" + op + "/rs/" + fgsrc + "/authkey/" + authkey,
			success: function(reply) {
						if (op == 'm' ) {
							ngBuTSCs1(reply);
						}
						if (op == 'n' ) {
							ngBuTSCs2(reply);
						}
					}
		});
	}
	function ngBuTSCs1(reply) {
		var ret = reply.split('--');
		var x = ret.length - 1;
		for (x in ret) {
			if (x == 0) {
				var modtc = ret[x];
			} else {
				var wo = '#ngt6t' + ret[x];
				$(wo).html(ngaximg);
				ngBuTCS(ret[x],modtc);
			}
		}
	}
	function ngBuTSCs2(reply) {
		var ret = reply.split('--');
		var x = ret.length - 1;
		for (x in ret) {
			var modtc = ret[x];
			ngBuTCSs1('m',modtc);
		}
	}
	// ---)
	function ngBuTCS(tn,mod) {
		$.ajax({
			type: "GET",
			url: "ngboost/action/admin/xaop/bt/tn/" + tn + "/m/" + mod + "/authkey/" + authkey,
			success: function(reply) {
						ngBuTSC(reply,tn);
					}
		});
	}
	function ngBuTSC(reply,tn) {
		var wo = '#ngt6t' + tn;
		var cc = reply.substr(0,2);
		var msg = reply.substr(2);
		switch (cc) {
			case '0,':
				$(wo).html(ngokimg + ' ' + msg);
				break;
			case '1,':
				$(wo).html(ngohimg + ' ' + msg);
				break;
			default:
				$(wo).html(ngkoimg + ' ' + msg);
		}
	}
	
	function ngOnC() {
		var sel = $('input[name=distro]:radio:checked').attr('value');
		ngUniCS(sel,'ws');
	}
		
	function ngVerReply(reply) {
		if (reply == '') {
			return false;
		}
		//if (reply.substring(0,2) <> '<!') {
		//	return true;
		//}
		return true;
	}
	

