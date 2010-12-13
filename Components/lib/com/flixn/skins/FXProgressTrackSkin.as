package com.flixn.skins
{
    import flash.display.Graphics;

    import mx.skins.Border;
    import mx.styles.StyleManager;
    import mx.utils.ColorUtil;

    public class FXProgressTrackSkin extends Border
    {
        public function FXProgressTrackSkin()
        {
            super();
        }

        override protected function updateDisplayList(width:Number, height:Number):void
        {
            super.updateDisplayList(width, height);

            var bevel:Boolean = getStyle('bevel');
            var bevelSize:uint = getStyle('bevelSize');
            if (!bevelSize)
                bevelSize = 1;

            var borderColor:uint = getStyle('borderColor');

            var fill:Boolean = getStyle('trackFill');
            var fillColors:Array = getStyle('trackFillColors');
            var fillAlphas:Array = getStyle('trackFillAlphas');

            var highlight:Boolean = getStyle('trackHighlight');
            var highlightAlphas:Array = getStyle("trackHighlightAlphas");

            StyleManager.getColorNames(fillColors);
		
            var borderColorDrk1:Number =
                ColorUtil.adjustBrightness2(borderColor, -60);
		
            var g:Graphics = graphics;
		
            g.clear();
		
            if (bevel) {
                drawRoundRect(
                    0, 0, width, height, 0,
                    borderColorDrk1, 1);

                drawRoundRect(bevelSize, bevelSize,
                              width - (bevelSize * 2), height - (bevelSize * 2), 0,
                              fillColors, fillAlphas,
                              verticalGradientMatrix(bevelSize, bevelSize,
                                    width - (bevelSize * 2), height - (bevelSize * 2)));
            } else {
                drawRoundRect(0, 0, width, height, 0,
                              fillColors, fillAlphas,
                              verticalGradientMatrix(0, 0, width, height));
            }

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