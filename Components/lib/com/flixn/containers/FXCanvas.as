package com.flixn.containers {

    import mx.containers.Canvas;
    import mx.styles.CSSStyleDeclaration;
    import mx.styles.StyleManager;

    [Style(name = "fill", type = "Boolean", inherit = "no")]
    [Style(name = "fillColors", type = "Array", arrayType = "uint", format = "Color", inherit = "no")]
    [Style(name = "fillAlphas", type = "Array", arrayType = "Number", inherit = "no")]
    [Style(name = "highlightAlphas", type = "Array", arrayType = "Number", inherit = "no")]

    public class FXCanvas extends Canvas
    {
        public function FXCanvas()
        {
            super();
        }

        override public function styleChanged(styleProp:String):void
        {
            super.styleChanged(styleProp);

            if (styleProp == "fillColors" || styleProp == "fillAlphas")
                invalidateDisplayList();
        }

        override protected function updateDisplayList(width:Number, height:Number):void
        {
            super.updateDisplayList(width, height);

            var fill:Boolean = getStyle('fill');
            var fillAlphas:Array = getStyle("fillAlphas");
            var fillColors:Array = getStyle("fillColors");
            var highlightAlphas:Array = getStyle("highlightAlphas");

            graphics.clear();

            if (fill) {
            drawRoundRect(0, 0, width, height, 0,
                          fillColors, fillAlphas,
                          verticalGradientMatrix(0, 0, width, height));

            drawRoundRect(0, height / 20, width, height / 1.8,
                          { tl: 1, tr: 1, bl: 0, br: 0 },
                          [ 0xFFFFFF, 0xFFFFFF ], highlightAlphas,
                          verticalGradientMatrix(0, 0, width, height/2));
            }
        }
    }
}