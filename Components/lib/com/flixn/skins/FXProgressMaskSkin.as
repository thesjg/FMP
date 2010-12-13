package com.flixn.skins
{
    import flash.display.Graphics;

    import mx.skins.Border;

    public class FXProgressMaskSkin extends Border
    {
        public function FXProgressMaskSkin()
        {
            super();
        }

        override protected function updateDisplayList(width:Number, height:Number):void
        {
            super.updateDisplayList(width, height);
            var g:Graphics = graphics;
            g.clear();
            g.beginFill(0xFFFF00);
            g.drawRect(0, 0, width, height);
            g.endFill();
        }
    }
}