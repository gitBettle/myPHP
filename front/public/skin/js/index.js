$(function(){
                            if($("#delAll")[0])$("#delAll")[0].disabled=false;
                            $("#delAll").click(function(){
                                $(this)[0].disabled=true;
                                $(this).parents("form").submit();
                            });
                            $("#addRecord").click(function(){
                                window.location.href=myUrl;
                            });
                            $("#alls").click(function(){
                                $(":checkbox").attr("checked",$(this)[0].checked);
                            });
                        });