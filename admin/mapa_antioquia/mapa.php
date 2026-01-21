<?php include './generic_clases_mapa.php'; ?>

<!-- css styles de cada mapa, departamento -->
<style type="text/css">
	.st0 {
		fill: #9b9b9b;
		stroke: #FFFFFF;
		stroke-miterlimit: 10;
	}

	.st1 {
		font-family: 'MyriadPro-Regular';
	}

	.st2 {
		font-size: 12.6512px;
	}

	.st3 {
		fill: #9b9b9b;
		stroke: #FFFFFF;
		stroke-miterlimit: 10;
	}

	.st4 {
		font-size: 13.0921px;
	}

	.st5 {
		fill: #9b9b9b;
		stroke: #FFFFFF;
		stroke-miterlimit: 10;
	}

	.st6 {
		font-size: 17.8172px;
	}

	.st7 {
		fill: #9b9b9b;
		stroke: #FFFFFF;
		stroke-miterlimit: 10;
	}

	.st8 {
		font-size: 12px;
	}

	.st9 {
		fill: #9b9b9b;
		stroke: #FFFFFF;
		stroke-miterlimit: 10;
	}

	.st10 {
		font-size: 18.8496px;
	}

	.st11 {
		fill: #9b9b9b;
		stroke: #FFFFFF;
		stroke-miterlimit: 10;
	}

	.st12 {
		fill: #9b9b9b;
		stroke: #FFFFFF;
		stroke-miterlimit: 10;
	}

	.st13 {
		font-size: 13px;
	}

	.st14 {
		fill: #9b9b9b;
		stroke: #FFFFFF;
		stroke-miterlimit: 10;
	}

	.st15 {
		font-size: 15.5858px;
	}

	.st16 {
		font-size: 16.3349px;
	}

	.st17 {
		font-size: 15.7204px;
	}

	.st18 {
		fill: #9b9b9b;
		stroke: #FFFFFF;
		stroke-miterlimit: 10;
	}

	.st19 {
		font-size: 16.9713px;
	}

	.st20 {
		font-size: 15px;
	}

	.st21 {
		font-size: 16.0877px;
	}

	.st22 {
		font-size: 16.1855px;
	}

	.st23 {
		font-size: 17.7154px;
	}

	.st24 {
		font-size: 16.2922px;
	}

	.st25 {
		font-size: 19.5642px;
	}

	.st26 {
		font-size: 17.3591px;
	}

	.st27 {
		font-size: 17.2367px;
	}

	.st28 {
		font-size: 16px;
	}

	.st29 {
		fill: #9b9b9b;
		stroke: #FFFFFF;
		stroke-miterlimit: 10;
	}

	.st30 {
		font-size: 15.6022px;
	}

	.st31 {
		font-size: 15.0981px;
	}

	.st32 {
		font-size: 14.4544px;
	}

	.st33 {
		font-size: 12.3131px;
	}

	.st34 {
		font-size: 15.6866px;
	}

	.st35 {
		font-size: 15.6721px;
	}

	.st36 {
		font-size: 17.4141px;
	}

	.st37 {
		font-size: 15.4552px;
	}

	.st38 {
		font-size: 21.0081px;
	}

	.st39 {
		font-size: 14.0919px;
	}

	.st40 {
		font-size: 14.9695px;
	}

	.st41 {
		fill: #9b9b9b;
		stroke: #FFFFFF;
		stroke-miterlimit: 10;
	}

	.st42 {
		font-size: 12.987px;
	}

	.st43 {
		fill: #9b9b9b;
		fill-opacity: 0.976;
	}

	.st44 {
		font-size: 11.0528px;
	}

	.st45 {
		fill: #9b9b9b;
		stroke: #FFFFFF;
		stroke-miterlimit: 10;
	}

	.st46 {
		font-size: 11.0191px;
	}

	.st47 {
		font-size: 15.6316px;
	}

	.st48 {
		font-size: 12.2129px;
	}

	.st49 {
		font-size: 9.948px;
	}

	.st50 {
		font-size: 11.8568px;
	}

	.st51 {
		font-size: 10.3304px;
	}

	.st52 {
		font-size: 12.0689px;
	}

	.st53 {
		font-size: 12.5359px;
	}

	.st54 {
		font-size: 10.9201px;
	}

	.st55 {
		font-size: 11.3569px;
	}

	.st56 {
		font-size: 13.942px;
	}

	.st57 {
		font-size: 18.3805px;
	}

	.st58 {
		font-size: 12.7606px;
	}

	.st59 {
		fill: #9b9b9b;
		fill-opacity: 0.976;
	}

	.st60 {
		font-size: 12.8524px;
	}

	.st61 {
		font-size: 13.0561px;
	}

	.st62 {
		font-size: 10.9596px;
	}

	.st63 {
		font-size: 11.4859px;
	}

	.st64 {
		enable-background: new;
	}

	.st65 {
		font-size: 11.79px;
	}

	.st66 {
		font-size: 12.9253px;
	}

	.st67 {
		font-size: 11.871px;
	}

	.st68 {
		font-size: 12.695px;
	}

	.st69 {
		font-size: 12.4693px;
	}

	.st70 {
		font-size: 14.2434px;
	}

	.st71 {
		font-size: 12.5274px;
	}

	.st72 {
		font-size: 12.1356px;
	}

	.st73 {
		font-size: 11.3403px;
	}

	.st74 {
		font-size: 11.22px;
	}

	.st75 {
		font-size: 12.1693px;
	}

	.st76 {
		font-size: 11.6947px;
	}

	.st77 {
		font-size: 12.772px;
	}

	.st78 {
		font-size: 11.1206px;
	}

	.st79 {
		font-size: 14.9296px;
	}

	.st80 {
		font-size: 11.9777px;
	}

	.st81 {
		font-size: 12.4211px;
	}

	.st82 {
		font-size: 13.6139px;
	}

	.st83 {
		font-size: 13.4806px;
	}

	.st84 {
		font-size: 9.7925px;
	}

	.st85 {
		font-size: 11.0679px;
	}

	.st86 {
		font-size: 11.5412px;
	}

	.st87 {
		font-size: 12.0478px;
	}

	.st88 {
		font-size: 13.6547px;
	}

	.st89 {
		font-size: 11.4547px;
	}

	.st90 {
		font-size: 10.5116px;
	}

	.st91 {
		font-size: 9.253px;
	}

	.st92 {
		font-size: 7.1755px;
	}

	.st93 {
		font-size: 6.6664px;
	}

	.st94 {
		font-size: 11.8736px;
	}

	.st95 {
		font-size: 13.7348px;
	}

	.st96 {
		font-size: 12.0301px;
	}

	.st97 {
		font-size: 10.7556px;
	}

	.st98 {
		font-size: 12.3426px;
	}

	.st99 {
		font-size: 11.4732px;
	}

	.st100 {
		font-size: 12.4685px;
	}

	.st101 {
		fill: #9b9b9b;
		fill-opacity: 0.976;
	}

	.st102 {
		font-size: 11.9917px;
	}

	.st103 {
		font-size: 11.9965px;
	}

	.st104 {
		font-size: 10.5515px;
	}

	.st105 {
		font-size: 12.0001px;
	}

	.st106 {
		font-size: 13.5493px;
	}

	.st107 {
		font-size: 13.1199px;
	}

	.st108 {
		font-size: 13.4572px;
	}

	.st109 {
		font-size: 12.7977px;
	}

	.st110 {
		fill: #9b9b9b;
		fill-opacity: 0.976;
	}

	.st111 {
		font-size: 11.8991px;
	}

	.st112 {
		font-size: 11.4396px;
	}

	.st113 {
		font-size: 8.8886px;
	}

	.st114 {
		font-size: 10.6042px;
	}

	.st115 {
		font-size: 10.6922px;
	}

	.st116 {
		font-size: 11.031px;
	}

	.st117 {
		font-size: 8.5495px;
	}

	.st118 {
		font-size: 10.5728px;
	}

	.st119 {
		font-size: 12.2768px;
	}

	.st120 {
		font-size: 13.6886px;
	}

	.st121 {
		font-size: 12.7415px;
	}

	.st122 {
		font-size: 11.6772px;
	}
</style>

<?php include './generic_municipios_svg_render.php'; ?>