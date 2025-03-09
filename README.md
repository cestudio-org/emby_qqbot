# emby_qqbot

这个项目很简单，他的目的是把Emby的WebHook信息转发到QQ，TG或者 WeChat等平台，方便管理
This project is used to synchronize Emby Media Server webhook information and QQ,TG and WeChat group messages

**https://local/?type=gocq&group_id=**

## 部署方式
使用Vercel部署，fork此仓库，然后直接部署就行，需要注意的是环境变量

分为三种情况

1、QQ(gocq,lgr,llob等)
```bash
QQAPI = http://127.0.0.1:3000
ACCESSTOKEN = 114514
```
2、WeChat
```bash
WXAPI = http://127.0.0.1:3000
WX_API_TOKEN = 114514
```
3、Tg
```bash
TGTOKEN = 114514
````