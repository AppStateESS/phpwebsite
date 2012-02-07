
/**
  * @author Hilmar Runge <ngwebsite.net>
  * @version 20110701
  */
	
	var ngaximg = '<img src="' + source_http + 'mod/ngboost/img/ajax10red.gif" alt="..." />';
	var ngokimg = '<img src="' + source_http + 'mod/ngboost/img/ok.10.gif" alt="ok" />';
	var ngohimg = '<img src="' + source_http + 'mod/ngboost/img/oh.10.gif" alt="oh" />';
	var ngkoimg = '<img src="' + source_http + 'mod/ngboost/img/ko.10.gif" alt="ko" />';
	var ngnoimg = '<img src="' + source_http + 'mod/ngboost/img/no.10.gif" alt="no" />';

	var ngprobar = 0;
	
	var ngselrel ='';
	
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
		var wo = '#ngmsgbu' + mod;
		$(wo).html(ngaximg);
		ngUniCS(mod,'b');
	}
	function ngBuTbls(mod) {
		// silent
		ngUniCS(mod,'bmt');
	}
	function ngBuSC(reply) {
		var ret = reply.split('--',3);
		var mod = ret[0];
		var cci = ret[1];
		var msg = ret[2];
		var wo = '#ngmsgbu' + mod;
		$(wo).html(cci);
		$('#ngbstcpfoot').append(msg);
	}
	function ngBuSCmod(reply) {
		$('#ngbstcpfoot').append(reply);
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
			var wo = '#ngmsgbu' + ret[x];
			$(wo).html(ngaximg);
			ngUniCS(ret[x],'B2');
		}
	}
	function ngBuAllSC(reply) {
		var ret = reply.split('--',3);
		var mod = ret[0];
		var cci = ret[1];
		var msg = ret[2];
		var wo1 = '#ngmsgbu' + mod;
		ngPro();
		$(wo1).html(cci);
		$('#ngbstcpfoot').append(msg);
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
		var opl = ret[0].length + ret[1].length + 4; // x
		var cnt = reply.substr(opl);
		$('#ngmsg').html(cnt);
		ngJqShow('#ngmsg',true);
	}
	function ngCheckNew(mod) {
		var wo = '#ngchn' + mod;
		$(wo).html(ngaximg);
		ngUniCS(mod,'cn');
	}
	function ngCheckNewSC(reply) {
		var ret = reply.split('--',3);
		var mod = ret[0];
		var vsn = ret[1];
		var opl = ret[0].length + ret[1].length + 4; // x
		var cnt = reply.substr(opl);
		$('#ngchnv' + mod).text(vsn);
		$('#ngchna' + mod).html(cnt);
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
		if (ngprobar > 0) {
			$(wo1).text(vsn);
		} else {
			ngUniCS('','ml');
		}
	}
		
	function ngInstall(mod) {
		ngUniCS(mod,'i');
	}	
	function ngInstallSC(reply) {
		var ret = reply.split('--',3);
		var cc = ret[0];
		var mod = ret[1];
		var cnt = ret[2];
		var wo = '#ngin' + mod;
		$('#ngmsg').html(cnt);
		if (cc == 'ok') {
			ngJqShow('#ngmsg',true);
		} else {
			ngJqShow('#ngmsg',false);
		}
	}
	
	function ngUnInstall(p) {
		var wo = '#ngun' + p;
		var msg = ngboostmsg040 + ' <b>' + p + '</b>';
		$(wo).fastConfirm({
			questionText: msg,
			onProceed: function(trigger) {ngUnInstallReplied(p,true);},
			onCancel: function(trigger) {ngUnInstallReplied(p,false);} 
		});
		// anything more here will run due fastconfirm is still not processed
	}	
	function ngRemoMod(p) {
		var wo = '#ngrmna' + p;
		var msg = ngboostmsg050 + ' <b>' + p + '</b>';
		$(wo).fastConfirm({
			questionText: msg,
			onProceed: function(trigger) {ngRemoReplied(p,true);},
			onCancel: function(trigger) {ngRemoReplied(p,false);} 
		});
		// anything more here will run due fastconfirm is still not processed
	}	
	function ngUnInstallReplied(p,answer) {
		if (answer) {
			// .. bu fs
			ngBu(p);
			// .. bu db
			ngBuTbls(p)
			ngUnInstallCS(p,p);
		} else {
			ngUnInstallCS(p,'void');
		}
	}
	function ngRemoReplied(p,answer) {
		if (answer) {
			ngRemoCS(p,p);
		} else {
			//
		}
	}
	function ngUnInstallCS(mod,confirm) {
		$.ajax({
			type: "GET",
			url: "ngboost/action/admin/xaop/un/p/" + mod + '/confirm/' + confirm + '/authkey/' + authkey,
			success: function(reply) {
						ngUnInstallSC(reply);
					}
		});
	}
	function ngRemoCS(mod,confirm) {
		$.ajax({
			type: "GET",
			url: "ngboost/action/admin/xaop/urm/p/" + mod + '/confirm/' + confirm + '/authkey/' + authkey,
			success: function(reply) {
						ngRemoSC(reply);
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
		if (cc == 'ok') {
			ngJqShow('#ngmsg',true);
			if (mod=='ngboost') {
				// bye ngboost
				setTimeout(
				"window.location = './boost/action/admin/tab/other_mods/authkey/' + authkey;",
				5000);
			}
		} else {
			ngJqShow('#ngmsg',false);
		}
	}
	function ngRemoSC(reply) {
		var ret = reply.split('--',3);
		var cc = ret[0];
		var mod = ret[1];
		var cnt = ret[2];
		if (cc == 'ok') {
			ngPlain('tsl');
		}
	}
	
	function ngUpdate(mod) {
		ngUniCS(mod,'up');
	}	
	function ngUpdateSC(reply) {
		var ret = reply.split('--',3);
		var cc = ret[0];
		var mod = ret[1];
		var opl = ret[0].length + ret[1].length + 4;
		var cnt = reply.substr(opl);
		var wo = '#ngup' + mod;
		$('#ngmsg').html(cnt);
		if (cc == 'ok') {
			ngJqShow('#ngmsg',true);
		} else {
			ngJqShow('#ngmsg',false);
		}
	}
	function ngShowDep(mod) {
		ngUniCS(mod,'sd');
	}
	function ngShowDepUpon(mod) {
		ngUniCS(mod,'su');
	}
	function ngPatos() {
		ngUniCS('','pas');
	}
	function ngListLog() {
		ngUniCS('','lbl');
	}
	function ngListLogE() {
		ngUniCS('','lel');
	}
	function ngFsPerms() {
		ngUniCS('','fsp');
	}
	function ngFsPermsLock() {
		ngUniCS('','fspz');
	}
	function ngFsPermsUnlock() {
		ngUniCS('','fspy');
	}
	function ngPickup(mod,x) {
		for (var i=0; i<x; i++) {
			var wom = '#ngpickuprm' + i + mod;
			var wop = '#ngpickuprp' + i + mod;
			$(wom).html('receive ');
			$(wop).html(ngaximg);
			ngUniCS(mod,'wgr',i);
		}
	}	
	function ngPickupSC(reply) {
		var ret = reply.split('--',5);
		var ix = ret[0];
		var ac = ret[1];
		var cc = ret[2];
		var mod = ret[3];
		var msg = ret[4];
		var wop = '#ngpickup' + ac + 'p' + ix + mod;
		var wom = '#ngpickup' + ac + 'm' + ix + mod;
		if (cc == '0') {
			var pic = ngokimg;
		} else {
			var pic = ngkoimg;
		}
		$(wop).html(pic);
		if (cc=='0') {
			switch (ac) {
				case 'r':
					// from receive trigger check
					wom = '#ngpickupcm' + ix + mod;
					wop = '#ngpickupcp' + ix + mod;
					$(wom).html(' ' + msg + ' ');
					$(wop).html(ngaximg);
					ngUniCS(mod,'wgc',ix);			
				break
				case 'c':
					// from check trigger decompress
					wom = '#ngpickupxm' + ix + mod;
					wop = '#ngpickupxp' + ix + mod;
					$(wom).html(' ' + msg + ' ');
					$(wop).html(ngaximg);
					ngUniCS(mod,'wgx',ix);			
				break
				case 'x':
					// from decompress trigger expand
					wom = '#ngpickupum' + ix + mod;
					wop = '#ngpickupup' + ix + mod;
					$(wom).html(' ' + msg + ' ');
					$(wop).html(ngaximg);
					ngUniCS(mod,'wgu',ix);			
				break
				case 'u':
					// from decompress done
					wom = '#ngpickupzz' + ix + mod;
					$(wom).html(' ' + msg + ' ');
					var woa = '#ngpickupa' + mod;
					$(woa).html('');
				break
			}
		} else {
			wom = '#ngpickupzz' + ix + mod;
			$(wom).html(' ' + msg + ' ');
		}
	}

	function ngSetSrc(reply) {
		var wo = '#ngbstcpfoot';
		$(wo).html(reply);
	}
	
	function ngNewMLtrSC(reply) {
		var ret = reply.split("\n");
		var x = ret.length - 1;
		for (x in ret) {
			var row = ret[x].split('--',2);
			var wo = '#ngmltr' + row[0];
			$(wo).html(row[1]);
		}
	}

	function ngRelSel() {
		ngselrel = $('#ngboostrsel').val() + '';
		ngUniCS('','sr');
	}
	
	function ngUniCS(ref,op,i) {
		var url = 'ngboost/action/admin/xaop/';
		switch (op) {
			case 'a':
				url = url + 'a/p/';
				break;
			case 'b': case 'B2':
				url = url + 'bm/p/';
				break;
			case 'bmt':
				url = url + 'bmt/p/';
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
			case 'cn':
				url = url + 'cn/p/';
				break;
			case 'C':
				url = url + 'C';
				break;
			case 'fsp':
				url = url + 'fsp';
				break;
			case 'fspy':
				url = url + 'fspy';
				break;
			case 'fspz':
				url = url + 'fspz';
				break;
			case 'h':
				url = url + 'h/h/';
				break;
			case 'i':
				url = url + 'in/p/';
				break;
			case 'lbl':
				url = url + 'lbl';
				break;
			case 'lel':
				url = url + 'lel';
				break;
			case 'ml':
				url = url + 'ml';
				break;
			case 'pa':
				url = url + 'pa/p/';
				break;
			case 'po':
				url = url + 'po/p/';
				break;
			case 'pas':
				url = url + 'pas';
				break;
			case 'sd':
				url = url + 'd/p/';
				break;
			case 'sr':
				url = url + 'sr/v/' + ngselrel;
				break;
			case 'su':
				url = url + 'u/p/';
				break;
			case 'up':
				url = url + 'up/p/';
				break;
			case 'wgr':
				url = url + 'tget/x/' + i + '/m/';
				break;
			case 'wgc':
				url = url + 'tchk/x/' + i + '/m/';
				break;
			case 'wgx':
				url = url + 'tdec/x/' + i + '/m/';
				break;
			case 'wgu':
				url = url + 'texp/x/' + i + '/m/';
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
							case 'bmt':
								ngBuSCmod(reply);
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
							case 'cn':
								ngCheckNewSC(reply);
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
							case 'fsp': case 'fspy': case 'fspz': case 'lbl': case 'lel':
								ngPlainSC(reply,'#ngmsgt61');
							break;
							case 'ml':
								ngNewMLtrSC(reply);
							break;
							case 'pa':
								ngPlainSC(reply,'#ngpato' + ref);
							break;
							case 'pas':
								ngPlainSC(reply,'#ngmsgt31');
							break;
							case 'po':
								ngPlainSC(reply,'#ngpatx' + ref);
							break;
							case 'sd':
								ngUniJqSC(reply,'#ngmsg');
							break;
							case 'sr':
								ngPlain('tsl');
							break;
							case 'su':
								ngUniJqSC(reply,'#ngmsg');
							break;
							case 'up':
								ngUpdateSC(reply);
							break;
							case 'wgr': case 'wgc': case 'wgx': case 'wgu':
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
			ngJqShow(to,false);
		}
	}
	
	function ngJqShow(at,posttf) {
		var ngjqclose=function(hash) {
			hash.w.hide();
			hash.o.remove();
			ngJqClose(at,posttf);
			};
		$(at).jqm({onHide: ngjqclose});
		$(at).jqmShow();
	}
	function ngJqClose(at,posttf) {
		if (posttf==true) {
			ngUniCS('','ml');
		}
	}
	
	function ngPro() {
		if (ngprobar > 1 ) {
			ngprobar = ngprobar - 1;
			//	$('#ngmsg').removeClass('jqmWindow');
			$('.ngallmsg').attr('style', 'display:inline');
			$('.ngallmsg').html(' ' + ngaximg + ' ' + ngboostmsg011 + ' <b>' + ngprobar + '</b> ' + ngboostmsg012);
		} else {
			ngprobar = 0;
			$('.ngallmsg').attr('style', 'display:none');
			$('.ngallmsg').html('&nbsp;');
			//	$('#ngmsg').addClass('jqmWindow');
		}
	}
	
	function ngPlain(op) {
		ngPlainCS(op);
	}
	function ngPlainCS(op) {
		var fb = '#ngmsgt41';
		switch (op) {
		case 'ldb':
			document.body.style.cursor = 'wait';
			fb = '#ngmsgt51';
			break;
		case 'ts': case 'tsl':
			fb = '#ngmsgt31';
			break;
		case'fs': case 'fsd':
			fb = '#ngmsgt61';
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
		document.body.style.cursor = 'auto';
	}

	function ngPop(op,k,v) {
		if (op == 'dy') {
			var wo = '#ngop' + v;
			var msg = ngboostmsg020;
			$(wo).fastConfirm({
				questionText: msg,
				onProceed: function(trigger) {ngPopCS(op,k,v);},
				onCancel: function(trigger) {return;} 
			});
			return;
		}
		if (op == 're') {
			var wo = '#ngop' + v;
			var msg = ngboostmsg030;
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
		//	$('#ngbstcpfoot').append(cnt);
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
		
	function ngPatoDesc(pat) {
		ngUniCS(pat,'po');
		$('.ngpat').hide();
		$('.ngpata' ).text('more');
		var wo = '#ngpata' + pat;
		$(wo).attr('href','javascript:ngPatoLdesc(\'' + pat + '\')');
		$(wo).text('less');
		wo = '#ngpatx' + pat;
		$(wo).html(ngaximg);
		$(wo).addClass('ngpat');
	}
		
	function ngPatoLdesc(pat) {
		var wo = '#ngpatx' + pat;
		$(wo).removeClass('ngpat');
		$('.ngpat').hide();
		$('.ngpata' ).text('more');
		$(wo).toggle();
		$(wo).addClass('ngpat');
		var st = $(wo).css('display');
		wo = '#ngpata' + pat;
		if (st == 'none') {
			$(wo).text('more');
		} else {
			$(wo).text('less');
		}
	}

	function ngPatoApply(pat) {
		var wo = '#ngpato' + pat;
		$(wo).html(ngaximg);
		ngUniCS(pat,'pa');
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
	