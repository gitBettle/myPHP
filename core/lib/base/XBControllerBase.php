<?php
class XBControllerBase extends BObject
{
    /**
     * @brief 渲染layout
     * @param string $viewContent view代码
     * @return string 解释后的view和layout代码
     */
    public function renderLayout($layoutFile,$viewContent)
    {
        if(file_exists($layoutFile))
        {
            //在layout中替换view
            $layoutContent = file_get_contents($layoutFile);
            $content = str_replace('{viewcontent}',$viewContent,$layoutContent);
            return $content;
        }
        else
            return $viewContent;
    }

    /**
     * @brief 渲染处理
     * @param string $viewFile 要渲染的页面
     * @param string or array $rdata 要渲染的数据
     * @param bool 渲染的方式 值: true:缓冲区; false:直接渲染;
     */
    public function renderView($viewFile,$rdata=null)
    {
        //要渲染的视图
        $renderFile = $viewFile.$this->extend;

        //检查视图文件是否存在
        if(file_exists($renderFile))
        {
            //控制器的视图(需要进行编译编译并且生成可以执行的php文件)
            if(stripos($renderFile,IWEB_PATH.'web/view/')===false)
            {
                //生成文件路径
                $runtimeFile = str_replace($this->getViewPath(),$this->module->getRuntimePath(),$viewFile.$this->defaultExecuteExt);

                //layout文件
                $layoutFile = $this->getLayoutFile().$this->extend;

                if(!file_exists($runtimeFile) || (filemtime($renderFile) > filemtime($runtimeFile)) || (file_exists($layoutFile) && (filemtime($layoutFile) > filemtime($runtimeFile))))
                {
                    //获取view内容
                    $viewContent = file_get_contents($renderFile);

                    //处理layout
                    $viewContent = $this->renderLayout($layoutFile,$viewContent);

                    //标签编译
                    $inputContent = $this->tagResolve($viewContent);

                    //创建文件
                    $fileObj  = new IFile($runtimeFile,'w+');
                    $fileObj->write($inputContent);
                    $fileObj->save();
                    unset($fileObj);
                }
            }
            else
            {
                $runtimeFile = $renderFile;
            }

            //引入编译后的视图文件
            $this->requireFile($runtimeFile,$rdata);
        }
        else
        {
            return false;
        }
    }

    /**
     * @brief 引入编译后的视图文件
     * @param string $__runtimeFile 视图文件名
     * @param mixed  $rdata         渲染的数据
     * @return string 编译后的视图数据
     */
    public function requireFile($__runtimeFile,$rdata)
    {
        //渲染的数据
        if(is_array($rdata))
            extract($rdata,EXTR_OVERWRITE);
        else
            $data=$rdata;

        unset($rdata);

        //渲染控制器数据
        $__controllerRenderData = $this->getRenderData();
        extract($__controllerRenderData,EXTR_OVERWRITE);
        unset($__controllerRenderData);

        //渲染module数据
        $__moduleRenderData = $this->module->getRenderData();
        extract($__moduleRenderData,EXTR_OVERWRITE);
        unset($__moduleRenderData);

        require($__runtimeFile);
    }

    /**
     * @brief 编译标签
     * @param string $content 要编译的标签
     * @return string 编译后的标签
     */
    public function tagResolve($content)
    {
        $tagObj = new ITag();
        return $tagObj->resolve($content);
    }

}