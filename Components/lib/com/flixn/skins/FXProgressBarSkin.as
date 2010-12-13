package com.flixn.skins
{
    import flash.display.Graphics;

    import mx.skins.Border;
    import mx.utils.ColorUtil;

    public class FXProgressBarSkin extends Border
    {
        public function FXProgressBarSkin()
        {
            super();
        }

        override protected function updateDisplayList(width:Number, height:Number):void
        {
            super.updateDisplayList(width, height);

            var barColor:Object = getStyle("barColor");
		
            var fill:Boolean = getStyle('barFill');
            var fillColors:Array = getStyle('barFillColors');
            var fillAlphas:Array = getStyle('barFillAlphas');

            var highlight:Boolean = getStyle('barHighlight');
            var highlightAlphas:Array = getStyle("barHighlightAlphas");


            if (!fillColors) {
                if (!barColor)
                    barColor = getStyle("themeColor");

                fillColors = [ barColor, barColor ];
            }
		
            var g:Graphics = graphics;
		
            g.clear();

            if (fill)
                drawRoundRect(0, 0, width, height, 0,
                              fillColors, fillAlphas,
                              verticalGradientMatrix(0, 0, width, height));

            if (highlight)
                drawRoundRect(0, height / 20, width, height / 1.8,
                              { tl: 0, tr: 0, bl: 0, br: 0 },
                              [ 0xFFFFFF, 0xFFFFFF ], highlightAlphas,
                              verticalGradientMatrix(0, 0, width, height/2));
        }
    }
}