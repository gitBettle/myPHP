<?php
class XBControllerBase extends BObject
{
    /**
     * @brief ��Ⱦlayout
     * @param string $viewContent view����
     * @return string ���ͺ��view��layout����
     */
    public function renderLayout($layoutFile,$viewContent)
    {
        if(file_exists($layoutFile))
        {
            //��layout���滻view
            $layoutContent = file_get_contents($layoutFile);
            $content = str_replace('{viewcontent}',$viewContent,$layoutContent);
            return $content;
        }
        else
            return $viewContent;
    }

    /**
     * @brief ��Ⱦ����
     * @param string $viewFile Ҫ��Ⱦ��ҳ��
     * @param string or array $rdata Ҫ��Ⱦ������
     * @param bool ��Ⱦ�ķ�ʽ ֵ: true:������; false:ֱ����Ⱦ;
     */
    public function renderView($viewFile,$rdata=null)
    {
        //Ҫ��Ⱦ����ͼ
        $renderFile = $viewFile.$this->extend;

        //�����ͼ�ļ��Ƿ����
        if(file_exists($renderFile))
        {
            //����������ͼ(��Ҫ���б�����벢�����ɿ���ִ�е�php�ļ�)
            if(stripos($renderFile,IWEB_PATH.'web/view/')===false)
            {
                //�����ļ�·��
                $runtimeFile = str_replace($this->getViewPath(),$this->module->getRuntimePath(),$viewFile.$this->defaultExecuteExt);

                //layout�ļ�
                $layoutFile = $this->getLayoutFile().$this->extend;

                if(!file_exists($runtimeFile) || (filemtime($renderFile) > filemtime($runtimeFile)) || (file_exists($layoutFile) && (filemtime($layoutFile) > filemtime($runtimeFile))))
                {
                    //��ȡview����
                    $viewContent = file_get_contents($renderFile);

                    //����layout
                    $viewContent = $this->renderLayout($layoutFile,$viewContent);

                    //��ǩ����
                    $inputContent = $this->tagResolve($viewContent);

                    //�����ļ�
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

            //�����������ͼ�ļ�
            $this->requireFile($runtimeFile,$rdata);
        }
        else
        {
            return false;
        }
    }

    /**
     * @brief �����������ͼ�ļ�
     * @param string $__runtimeFile ��ͼ�ļ���
     * @param mixed  $rdata         ��Ⱦ������
     * @return string ��������ͼ����
     */
    public function requireFile($__runtimeFile,$rdata)
    {
        //��Ⱦ������
        if(is_array($rdata))
            extract($rdata,EXTR_OVERWRITE);
        else
            $data=$rdata;

        unset($rdata);

        //��Ⱦ����������
        $__controllerRenderData = $this->getRenderData();
        extract($__controllerRenderData,EXTR_OVERWRITE);
        unset($__controllerRenderData);

        //��Ⱦmodule����
        $__moduleRenderData = $this->module->getRenderData();
        extract($__moduleRenderData,EXTR_OVERWRITE);
        unset($__moduleRenderData);

        require($__runtimeFile);
    }

    /**
     * @brief �����ǩ
     * @param string $content Ҫ����ı�ǩ
     * @return string �����ı�ǩ
     */
    public function tagResolve($content)
    {
        $tagObj = new ITag();
        return $tagObj->resolve($content);
    }

}