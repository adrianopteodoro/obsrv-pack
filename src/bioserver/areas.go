package bioserver

import (
	"container/list"
)

type Areas struct {
	areas *list.List
}

func NewAreas() (rcvr *Areas) {
	rcvr = &Areas{}
	rcvr.areas = list.New()
	rcvr.areas.PushFront(NewArea(1, "East Town", "<BODY><SIZE=3>standard rules<END>", Area.STATUS_ACTIVE))
	rcvr.areas.PushFront(NewArea(2, "West Town", "<BODY><SIZE=3>individual games<END>", Area.STATUS_ACTIVE))
	return
}
func (rcvr *Areas) GetAreaCount() int {
	return len(rcvr.areas)
}
func (rcvr *Areas) GetDescription2(areanumber int) string {
	area, ok := areas[areanumber-1].(*Area)
	if !ok {
		panic("XXX Cast fail for *parser.GoCastType")
	}
	return area.getDescription()
}
func (rcvr *Areas) GetName2(areanumber int) string {
	area, ok := areas[areanumber-1].(*Area)
	if !ok {
		panic("XXX Cast fail for *parser.GoCastType")
	}
	return area.getName()
}
func (rcvr *Areas) GetStatus2(areanumber int) byte {
	area, ok := areas[areanumber-1].(*Area)
	if !ok {
		panic("XXX Cast fail for *parser.GoCastType")
	}
	return area.getStatus()
}
