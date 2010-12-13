package com.flixn.containers {

    import mx.containers.Canvas;
    import mx.styles.CSSStyleDeclaration;
    import mx.styles.StyleManager;

    public class FXTCanvas extends Canvas
    {
        public function FXTCanvas()
        {
            super();
        }

        override protected function updateDisplayList(width:Number, height:Number):void
        {
            super.updateDisplayList(width, height);

            graphics.clear();
            //border.clear();
        }
    }
}